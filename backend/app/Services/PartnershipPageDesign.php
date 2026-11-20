<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Page;
use App\Models\PageSection;
use App\Sections\SectionRegistry;
use App\Support\ContentCache;
use Illuminate\Support\Facades\DB;

class PartnershipPageDesign
{
    private const Root = 'partnerships';

    private const Corporate = self::Root.'/corporate';

    private const Academic = self::Root.'/academic';

    private const Strategic = self::Corporate.'/strategic-and-founding-partnerships';

    private const Schools = self::Corporate.'/schools-centers-coes-and-lab-partnerships';

    private const Sponsored = self::Corporate.'/sponsored-research';

    private const Enquiry = ['cta_label' => 'Partner with us', 'cta_url' => '#enquiry:partnership'];

    private const Photos = [
        'p011' => 'Two students comparing project work on a laptop',
        'p028' => 'Engineer using a tablet on the factory floor',
        'p035' => 'Industry professional inspecting equipment in a factory',
        'p044' => 'Hand writing on a glass board',
        'p053' => 'Two students working on a manufacturing machine',
        'p079' => 'Student working at a computer',
        'p096' => 'Colleagues discussing work at a laptop',
        'p099' => 'Illustrative portrait in a workplace',
        'p101' => 'Colleagues in conversation in a meeting room',
        'p104' => 'Researchers reviewing materials together',
        'p105' => 'Student assembling a small robot arm',
        'p110' => 'Students working in a classroom',
        'p114' => 'Researcher working at a laptop',
        'p118' => 'Team testing a robotic system in a lab',
        'p122' => 'Three colleagues discussing a partnership',
        'p130' => 'Engineer working on a laptop',
        'p141' => 'Two collaborators reviewing work at a desk',
        'p145' => 'Three colleagues talking around a table',
    ];

    private array $images = [];

    public function __construct(private MediaUploader $uploader) {}

    public function apply(): void
    {
        if (! Page::query()->where('path', self::Root)->exists()) {
            return;
        }

        DB::transaction(function (): void {
            $pages = Page::query()->whereIn('path', [
                self::Root, self::Corporate, self::Academic, self::Strategic, self::Schools, self::Sponsored,
            ])->orderBy('id')->lockForUpdate()->get()->keyBy('path');
            $pages->load('sections');

            $corporate = $pages->get(self::Corporate);
            $leadership = $corporate ? $this->find($corporate, 'testimonials')?->data ?? [] : [];
            $contacts = $corporate ? $this->find($corporate, 'contact_band')?->data ?? [] : [];

            foreach ($pages as $path => $page) {
                $sections = [
                    $this->hero($page),
                    $this->write($page, 'logo_strip', [
                        'heading' => 'Partners', 'layout' => 'band', 'category' => null, 'sample_when_empty' => true,
                    ]),
                    ...match ($path) {
                        self::Root => $this->overview($page),
                        self::Corporate => $this->corporate($page),
                        self::Academic => $this->academic($page),
                        self::Strategic, self::Sponsored => $this->collaborationAreas($page),
                        self::Schools => $this->partnershipModels($page),
                    },
                ];

                if ($path !== self::Academic) {
                    $sections[] = $this->industryStories($page);
                }

                $sections[] = $this->leadership($page, $leadership);
                $sections[] = $this->write($page, 'news_carousel', [
                    'heading' => 'News & Insights', 'cta_label' => 'View all insights', 'cta_url' => '/media',
                    'sample_image' => $this->photo('p110'),
                ]);
                $closing = $this->closing($page, $contacts);

                $placed = array_map(fn (PageSection $section): int => $section->id, [...$sections, $closing]);
                $additional = $page->sections->whereNotIn('id', $placed);

                foreach ([...$sections, ...$additional, $closing] as $index => $section) {
                    $section->fill([
                        'sort_order' => $index, 'is_visible' => true, 'data' => $this->enableItems($section->data ?? []),
                    ])->save();
                }

                $page->syncMediaUsage();
            }
        });

        ContentCache::flush();
    }

    private function hero(Page $page): PageSection
    {
        [$title, $photo] = match ($page->path) {
            self::Corporate => ['The platform for thought leaders to come together and power manufacturing innovation', 'p145'],
            self::Academic => ['Building Industry-Integrated Engineering Education for the Future of Manufacturing', 'p145'],
            self::Strategic => ['Strategic & Founding Partnerships', 'p122'],
            self::Schools => ['Schools, Centers, CoEs & Lab Partnerships', 'p145'],
            self::Sponsored => ['Sponsored Research', 'p145'],
            default => ['Built with Industry. Backed by Global Expertise.', 'p145'],
        };
        $section = $this->find($page, 'page_hero');
        $data = $section?->data ?? [];
        $links = $page->path === self::Root
            ? ['cta_label' => 'Corporate partnerships', 'cta_url' => '/'.self::Corporate, 'secondary_label' => 'Academic partnerships', 'secondary_url' => '/'.self::Academic]
            : self::Enquiry;

        return $this->write($page, 'page_hero', [
            ...$links,
            'title' => $title,
            'eyebrow' => $data['eyebrow'] ?? ($page->path === self::Academic ? 'Academic Partnership' : $page->title),
            'body' => $data['body'] ?? 'Explore how academic institutions and industry can work with NAMTECH on engineering education, applied research and skills development.',
            'layout' => 'split',
            'image' => $this->photo($photo),
        ], $section);
    }

    private function overview(Page $page): array
    {
        $stories = $this->find($page, 'media_cards');
        if ($stories) {
            $items = $stories->data['items'] ?? [];
            foreach ($items as &$item) {
                $item['image'] = ($item['title'] ?? null) === 'Siemens' ? $this->photo('p035') : ($item['image'] ?? $this->photo('p101'));
                if (($item['title'] ?? null) === 'Siemens') {
                    $item['link_url'] ??= '/partnerships/stories/siemens';
                }
            }

            $this->write($page, 'media_cards', ['heading' => 'Selected Industry Collaborations', 'layout' => 'story', 'items' => $items, 'card_link_label' => 'Read story'], $stories);
        }

        $showcase = $this->find($page, 'partners_showcase');
        if ($showcase) {
            $this->write($page, 'partners_showcase', ['hide_logos' => true, 'show_stories' => true, 'sample_image' => $this->photo('p035')], $showcase);
        }

        return array_values(array_filter([$stories, $showcase]));
    }

    private function corporate(Page $page): array
    {
        $heading = 'Why partners choose to build with NAMTECH';
        $benefits = $this->find($page, 'media_cards', $heading) ?? $this->find($page, 'media_cards', 'Here’s Why Leading Corporates Build with NAMTECH');
        $stats = $this->find($page, 'stat_cards');
        $items = $benefits?->data['items'] ?? $stats?->data['items'] ?? [];
        $items = $this->photoCards($items, ['p105', 'p101', 'p079'], $this->exampleAreas());
        if ($stats) {
            $this->write($page, 'stat_cards', ['heading' => 'The manufacturing opportunity'], $stats);
        }

        $opportunities = $this->find($page, 'tabs_media');
        $tabs = $opportunities?->data['tabs'] ?? [];
        $paths = [self::Strategic, self::Schools, self::Sponsored];
        $names = ['Strategic & Founding Partnership', 'Schools, Centers, CoEs & Lab Partnerships', 'Sponsored Research'];
        $photos = ['p096', 'p101', 'p104'];
        foreach ($paths as $index => $path) {
            $tab = $tabs[$index] ?? ['label' => $names[$index], 'title' => $names[$index], 'text' => $this->exampleAreas()[$index]['body']];
            $tabs[$index] = [
                ...$tab,
                'image' => $this->photo($photos[$index]),
                'links' => ($tab['links'] ?? []) ?: [['label' => 'Explore more', 'url' => '/'.$path]],
            ];
        }

        return [
            $this->write($page, 'media_cards', ['heading' => $heading, 'layout' => 'feature', 'tone' => 'white', 'items' => $items], $benefits, 'partnership-benefits'),
            $this->write($page, 'tabs_media', [
                'heading' => 'Explore Partnership Opportunities with Us', 'eyebrow' => 'Partnership Opportunities',
                'layout' => 'card', 'tone' => 'gradient', 'tabs' => $tabs,
            ], $opportunities),
        ];
    }

    private function collaborationAreas(Page $page): array
    {
        $section = $this->find($page, 'media_cards', 'Partnership Areas')
            ?? $this->find($page, 'media_cards', 'How Can We Work Together?')
            ?? $this->find($page, 'card_grid');
        $intro = $this->find($page, 'intro');
        $items = $section?->data['items'] ?? $section?->data['cards'] ?? [];
        $photos = $page->path === self::Sponsored ? ['p011', 'p104', 'p053'] : ['p105', 'p101', 'p079'];
        $heading = $intro ? 'Partnership Areas' : 'How Can We Work Together?';
        if ($intro) {
            $this->write($page, 'intro', ['heading' => 'How Can We Work Together?', 'icon' => null], $intro);
        }

        $areas = $this->write($page, 'media_cards', [
            'heading' => $heading, 'layout' => 'feature', 'tone' => 'white',
            'items' => $this->photoCards($items, $photos, $this->exampleAreas()),
        ], $section, 'partnership-areas');

        return $intro ? [$intro, $areas] : [$areas];
    }

    private function partnershipModels(Page $page): array
    {
        $intro = $this->write($page, 'intro', [
            'heading' => 'How Can We Work Together?', 'icon' => null,
            'body' => $this->find($page, 'intro')?->data['body'] ?? '<p>Explore ways to build schools, centers and experiential labs with NAMTECH.</p>',
        ]);
        $modelTabs = $this->find($page, 'tabs', 'Partnership Models');
        $tabs = $modelTabs?->data['tabs'] ?? [];
        $panels = [];
        foreach ([
            ['school-partnerships', 'School-level Partnerships', 'Explore MET Innovation Schools', '/schools', 'building'],
            ['center-partnerships', 'Center and CoE Partnerships', 'MET Innovation Centers', '/schools', 'factory'],
            ['labs-partnership', 'Lab Partnerships', 'Labs Partnership', '/academics/labs-and-infrastructure', 'flask'],
        ] as $index => [$anchor, $label, $heading, $url, $icon]) {
            $section = $page->sections->firstWhere('anchor', $anchor)
                ?? $this->find($page, $index === 2 ? 'card_grid' : 'media_cards', $heading);
            $items = $section?->data['items'] ?? $section?->data['cards'] ?? [];
            if ($index === 2) {
                $items = $items ?: [['title' => 'Equipment'], ['title' => 'Full Lab'], ['title' => 'Training']];
                $labExamples = [
                    'Contribute industry-grade equipment for hands-on training and applied experimentation.',
                    'Co-design a teaching lab around equipment, workflows and projects used in industry.',
                    'Develop practical workshops and demonstrations with faculty and your technical team.',
                ];
                $sample = ($section?->data['eyebrow'] ?? null) === 'Sample content';
                foreach ($items as $i => &$item) {
                    if (blank($item['body'] ?? null)) {
                        $item['body'] = $labExamples[$i] ?? 'Explore hands-on learning opportunities with NAMTECH.';
                        $sample = true;
                    }
                }
                unset($item);
            } else {
                $sample = ! $items || ($section?->data['eyebrow'] ?? null) === 'Sample content';
            }

            $fromDesign = ['icon' => $icon, 'title' => $heading, 'body' => 'Explore an example of how academic and industry teams can build hands-on engineering programs together.', 'link_url' => $url];
            $items = array_map(fn (array $item): array => [
                ...$item, 'icon' => $item['icon'] ?? $icon, 'image' => $item['image'] ?? $this->photo('p105'),
            ], $items ?: [$fromDesign]);

            $panels[] = $this->write($page, 'media_cards', [
                'heading' => $heading, 'layout' => 'partnership_tiles', 'card_link_label' => 'Explore more',
                'items' => $items, 'eyebrow' => $sample ? 'Sample content' : null,
            ], $section, $anchor);
            $tabs[$index] = [
                ...($tabs[$index] ?? ['label' => $label, 'body' => '<p>Explore ways to work together on '.e(strtolower($label)).'.</p>']),
                'panel_anchor' => $anchor,
            ];
        }
        $models = $this->write($page, 'tabs', ['heading' => 'Partnership Models', 'layout' => 'section_panels', 'tabs' => $tabs], $modelTabs, 'partnership-models');

        $ecosystem = $this->find($page, 'media_cards', 'Co-Building the Ecosystem') ?? $this->find($page, 'card_grid', 'Co-Building the Ecosystem');
        $ecosystem = $this->write($page, 'media_cards', [
            'heading' => 'Co-Building the Ecosystem', 'layout' => 'labs',
            'items' => $this->photoCards($ecosystem?->data['items'] ?? $ecosystem?->data['cards'] ?? [], ['p118', 'p104', 'p053'], $this->exampleAreas()),
        ], $ecosystem, 'co-building-ecosystem');

        return [$intro, $models, ...$panels, $ecosystem];
    }

    private function academic(Page $page): array
    {
        $story = $this->find($page, 'media_cards', 'Our Global Academic Collaborations in Action')
            ?? $this->find($page, 'media_cards', 'Global Academic Collaborations In Action');
        $items = $story?->data['items'] ?? [];
        if (! $items) {
            $items = [['title' => 'Sample Academic Collaboration', 'body' => 'A sample collaboration could combine practical classroom teaching with a project designed jointly by two institutions.', 'image' => $this->photo('p044')]];
        }
        foreach ($items as $index => $item) {
            if (str_contains($item['title'] ?? '', 'TUM ASIA') || str_contains($item['title'] ?? '', 'TUM Asia')) {
                $item['image'] = $this->photo('p044');
                $item['link_url'] ??= 'https://tum-asia.edu.sg/';
                $item['sample_metrics'] = empty($item['metrics']) || ($item['sample_metrics'] ?? false);
                $item['metrics'] = ($item['metrics'] ?? []) ?: [
                    ['value' => '14', 'label' => 'Joint learning activities'],
                    ['value' => '93%', 'label' => 'Participant feedback'],
                    ['value' => '879+', 'label' => 'Learners reached'],
                ];
                unset($items[$index]);
                array_unshift($items, $item);
                break;
            }
        }

        $benefits = $this->find($page, 'media_cards', 'Why Global Academics and Researchers Choose to Work with NAMTECH')
            ?? $this->find($page, 'media_cards', 'Here’s Why Leading Academic Institutions Partner With NAMTECH')
            ?? $this->find($page, 'card_grid', 'Here’s Why Leading Academic Institutions Partner With NAMTECH');
        $intro = $this->find($page, 'intro', 'Manufacturing Through Applied Research');
        $research = $this->find($page, 'image_text', 'Research done by NAMTECH');
        $pillars = $this->find($page, 'pillars') ?? $this->find($page, 'card_grid', 'Co-Create The Future With NAMTECH');
        $pillarItems = $this->photoCards($pillars?->data['items'] ?? $pillars?->data['cards'] ?? [], ['p130', 'p130', 'p141', 'p130', 'p114', 'p104'], $this->exampleAreas());

        return [
            $this->write($page, 'media_cards', [
                'heading' => 'Our Global Academic Collaborations in Action', 'layout' => 'story', 'head_align' => 'center',
                'items' => array_values($items), 'card_link_label' => 'Read more',
            ], $story, 'academic-collaborations'),
            $this->write($page, 'media_cards', [
                'heading' => 'Why Global Academics and Researchers Choose to Work with NAMTECH', 'layout' => 'feature', 'tone' => 'gradient',
                'items' => $this->photoCards($benefits?->data['items'] ?? $benefits?->data['cards'] ?? [], ['p105', 'p101', 'p079'], $this->exampleAreas()),
            ], $benefits, 'academic-benefits'),
            $this->write($page, 'image_text', [
                'heading' => 'Research done by NAMTECH', 'layout' => 'research', 'image_position' => 'left', 'image' => $this->photo('p110'),
                'body' => $research?->data['body'] ?? $intro?->data['body'] ?? '<p>Faculty and students work with academic and industry collaborators to explore practical questions in engineering and manufacturing.</p>',
            ], $research ?? $intro, 'academic-research'),
            $this->write($page, 'card_grid', [
                'eyebrow' => 'Sample research themes', 'heading' => null, 'columns' => '3', 'style' => 'joined_light',
                'cards' => $page->sections->firstWhere('anchor', 'academic-research-themes')?->data['cards'] ?? [
                    ['icon' => 'factory', 'title' => 'Smart manufacturing', 'body' => 'Explore how production data can support decisions about process improvement.'],
                    ['icon' => 'gear', 'title' => 'Robotics and automation', 'body' => 'Prototype and evaluate robotic systems for practical manufacturing tasks.'],
                    ['icon' => 'leaf', 'title' => 'Sustainable engineering', 'body' => 'Study energy, materials and resource use in manufacturing systems.'],
                ],
                'cta_label' => 'Explore research', 'cta_url' => '/research-and-consultancy/research',
            ], anchor: 'academic-research-themes'),
            $this->write($page, 'pillars', [
                'heading' => 'Ways to Collaborate', 'layout' => 'split',
                'items' => array_map(fn (array $item): array => [...$item, 'text' => $item['body']], $pillarItems),
            ], $pillars, 'ways-to-collaborate'),
        ];
    }

    private function industryStories(Page $page): PageSection
    {
        $existing = $page->path === self::Root ? null : $this->find($page, 'partners_showcase');
        if ($page->path === self::Root) {
            $existing = $this->find($page, 'partners_showcase', 'Collaboration for a Cause: Meet our Industry Partners');
        }
        $faq = $this->find($page, 'faq', 'Meet our Industry Partners');
        if ($faq) {
            $this->write($page, 'faq', ['heading' => 'Industry Partner Profiles'], $faq);
        }

        return $this->write($page, 'partners_showcase', [
            'heading' => 'Collaboration for a Cause: Meet our Industry Partners',
            'body' => $existing?->data['body'] ?? 'Our curriculum is co-designed with industry to align with workforce needs. These collaborations connect students with hands-on experience and industry mentorship.',
            'eyebrow' => 'Partnerships', 'category' => 'industry', 'hide_logos' => true, 'show_stories' => true, 'stories_layout' => 'photo',
            'tone' => in_array($page->path, [self::Root, self::Corporate], true) ? 'white' : 'gradient',
            'story_images' => [
                ['slug' => 'siemens', 'image' => $this->photo('p035')],
                ['slug' => 'cisco', 'image' => $this->photo('p028')],
            ],
            'sample_image' => $this->photo('p035'),
        ], $existing, 'industry-collaborations');
    }

    private function leadership(Page $page, array $shared): PageSection
    {
        $existing = $this->find($page, 'testimonials');
        $items = ($existing?->data['items'] ?? []) ?: ($shared['items'] ?? []);
        if (! $items) {
            $items = [[
                'name' => 'Sample leadership perspective',
                'role' => 'Illustrative testimonial',
                'quote' => 'A joint project can give students valuable experience applying engineering concepts to a manufacturing challenge.',
            ]];
        }

        return $this->write($page, 'testimonials', [
            'heading' => 'Leadership Perspectives', 'layout' => 'spotlight', 'tone' => 'lavender',
            'image' => $this->photo('p099'), 'items' => $items, 'photo_caption' => 'Illustrative photo',
            ...($page->path === self::Academic ? [] : ['cta_label' => 'View all articles', 'cta_url' => '/media']),
        ], $existing, 'leadership-perspectives');
    }

    private function closing(Page $page, array $shared): PageSection
    {
        $existing = $this->find($page, 'contact_band') ?? $this->find($page, 'cta_band');
        if ($page->path === self::Academic) {
            return $this->write($page, 'cta_band', [
                'heading' => 'Co-Create With Us', 'tone' => 'lavender', ...self::Enquiry,
                'body' => $existing?->data['body'] ?? 'Tell us how your institution would like to collaborate on teaching, research or engineering education.',
            ], $existing, 'partner-with-us');
        }

        $details = [...$shared, ...($existing?->data ?? [])];

        return $this->write($page, 'contact_band', [
            ...$details, ...self::Enquiry, 'heading' => 'Co-Create With Us',
            'body' => $details['body'] ?? 'Talk with our team about collaborating on engineering education, practical training and applied research.',
        ], $existing, 'partner-with-us');
    }

    private function exampleAreas(): array
    {
        return [
            ['icon' => 'building', 'title' => 'Sample: Infrastructure development', 'body' => 'Design a practical teaching environment around equipment used by industry.'],
            ['icon' => 'people', 'title' => 'Sample: Workforce development', 'body' => 'Develop hands-on training for engineering students and technical teams.'],
            ['icon' => 'flask', 'title' => 'Sample: Applied research', 'body' => 'Explore a joint research project around a practical manufacturing problem.'],
        ];
    }

    private function photoCards(array $items, array $photos, array $fallback): array
    {
        $cards = [];
        foreach (array_values($items ?: $fallback) as $index => $item) {
            $body = $item['body'] ?? $item['text'] ?? null;
            $cards[] = [
                ...$item,
                'title' => $item['title'] ?? $item['label'] ?? $fallback[0]['title'],
                'body' => $body ?: 'Sample: '.$fallback[0]['body'],
                'image' => isset($photos[$index]) ? $this->photo($photos[$index]) : ($item['image'] ?? $this->photo($photos[0])),
            ];
        }

        return $cards;
    }

    private function find(Page $page, string $type, ?string $heading = null): ?PageSection
    {
        return $page->sections->first(fn (PageSection $section): bool => $section->type === $type
            && ($heading === null || ($section->data['heading'] ?? null) === $heading));
    }

    private function write(Page $page, string $type, array $data, ?PageSection $section = null, ?string $anchor = null): PageSection
    {
        $section ??= $anchor
            ? $page->sections->firstWhere('anchor', $anchor)
            : $this->find($page, $type);

        $attributes = [
            'type' => $type,
            'data' => SectionRegistry::normalize($type, $this->enableItems([...($section?->data ?? []), ...$data])),
            'anchor' => $anchor ?? $section?->anchor,
            'is_visible' => true,
        ];

        if ($section) {
            $section->fill($attributes)->save();

            return $section;
        }

        $section = $page->sections()->create([...$attributes, 'sort_order' => $page->sections->count()]);
        $page->sections->push($section);

        return $section;
    }

    private function photo(string $name): int
    {
        return $this->images[$name] ??= Media::query()
            ->whereIn('original_name', [$name.'.jpg', 'partnership-'.$name.'.webp'])
            ->where('status', 'ready')->value('id')
            ?? $this->uploader->storePath(
                resource_path('images/partnerships/'.$name.'.webp'),
                'partnership-'.$name.'.webp',
                ['alt' => self::Photos[$name]],
                sync: true,
            )->id;
    }

    private function enableItems(array $data): array
    {
        unset($data['_hidden']);

        return array_map(fn (mixed $value): mixed => is_array($value) ? $this->enableItems($value) : $value, $data);
    }
}
