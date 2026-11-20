<?php

namespace App\Services;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Post;
use App\Sections\SectionRegistry;
use App\Support\ContentCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImpactPageDesign
{
    private const StoriesUrl = '/impact/stories';

    private const FeaturedStory = 'iptp-graduate-to-smart-manufacturing-engineer-germany';

    private const SampleCase = 'sample-manufacturing-case-study';

    private const Photos = [
        'p017' => 'People working in a bright shared space',
        'p022' => 'Industrial robot in an automated manufacturing lab',
        'p028' => 'Technician using equipment on a factory floor',
        'p030' => 'Wind turbines on a snowy hillside',
        'p033' => 'Researchers working with plants in a laboratory',
        'p039' => 'Group of smiling students outdoors',
        'p045' => 'Curved library bookshelves',
        'p049' => 'Two colleagues looking at a laptop',
        'p050' => 'Students and educators discussing a project',
        'p099' => 'Illustrative portrait in a workplace',
        'p102' => 'Analyst reviewing information on a monitor',
        'p109' => 'Student working at a laptop',
        'p114' => 'Researcher working at a laptop',
        'p120' => 'Student working on an automation rig',
        'p124' => 'Student writing notes next to a laptop',
        'p127' => 'Researchers working together in a laboratory',
        'p128' => 'Student in a graduation gown',
        'p136' => 'Plant cuttings being studied in a laboratory',
        'p139' => 'Students testing a robotic hand with an instructor',
    ];

    private array $media = [];

    public function __construct(private MediaUploader $uploader) {}

    public function apply(): void
    {
        DB::transaction(function (): void {
            $page = Page::query()->where('path', 'impact')->lockForUpdate()->first();
            if (! $page) {
                return;
            }

            $caseStudy = $this->prepareStories();
            $this->landing($page, $caseStudy);
            $archive = Page::query()->where('path', 'impact/stories')->first();
            if ($archive) {
                $hero = $archive->sections()->where('type', 'page_hero')->first();
                $this->write($archive, $hero, 'page_hero', 'impact-stories-hero', [
                    'layout' => 'plain', 'title' => 'Impact Stories', 'image' => null, 'eyebrow' => null,
                    'body' => $this->copy($hero?->data['body'] ?? null, 'Explore stories from NAMTECH students, research teams, faculty and industry partners.'),
                ])->update(['sort_order' => 0]);
                $archive->syncMediaUsage();
            }
        });

        ContentCache::flush();
    }

    private function landing(Page $page, Post $caseStudy): void
    {
        $page->load('sections');
        $sections = $page->sections;
        $mediaCards = $sections->where('type', 'media_cards')->where('is_visible', true)->values();
        $hero = $sections->firstWhere('type', 'page_hero');
        $institution = $sections->firstWhere('anchor', 'institutional-impact')
            ?? $sections->where('type', 'card_grid')->first();
        $institutionStories = $sections->firstWhere('anchor', 'institutional-impact-stories') ?? $mediaCards->get(0);
        $student = $sections->firstWhere('type', 'image_mosaic');
        $studentStories = $sections->firstWhere('anchor', 'student-impact-stories') ?? $mediaCards->get(1) ?? $sections->firstWhere('type', 'post_list');
        $logos = $sections->firstWhere('type', 'logo_strip');
        $social = $sections->firstWhere('anchor', 'social-impact')
            ?? $sections->where('type', 'card_grid')->first(fn (PageSection $section): bool => Str::contains(Str::lower($section->data['heading'] ?? ''), 'social impact'))
            ?? $sections->firstWhere('type', 'card_tabs');
        $socialStories = $sections->firstWhere('anchor', 'social-impact-stories') ?? $mediaCards->get(2);
        $voices = $sections->firstWhere('type', 'testimonials');
        $closing = $sections->firstWhere('type', 'cta_band');

        $ordered = [
            $this->write($page, $hero, 'page_hero', 'impact-hero', [
                'title' => 'Power of a Collaborative Institution', 'eyebrow' => null, 'image' => $this->photo('p039'), 'layout' => 'split',
                'body' => $this->copy($hero?->data['body'] ?? null, 'NAMTECH brings together learners, educators and industry partners to connect engineering education with practical manufacturing challenges.'),
                'statistics_group' => $hero?->data['statistics_group'] ?? 'impact',
            ]),
            $this->write($page, $institution, 'card_grid', 'institutional-impact', [
                'heading' => 'NAMTECH’s Institutional Impact', 'style' => 'joined_mobile_carousel', 'columns' => '3', 'tone' => 'white',
                'body' => $this->copy($institution?->data['body'] ?? null, 'Explore how hands-on engineering education connects learners with manufacturing practice, innovation and sustainability.'),
                'cards' => $this->cards($institution?->data['cards'] ?? [], [
                    ['icon' => 'factory', 'title' => 'Engineering Talent Transformation', 'body' => 'Students build practical engineering skills through projects based on manufacturing challenges.'],
                    ['icon' => 'pencil', 'title' => 'Innovation for the Next Generation', 'body' => 'Hands-on projects give learners room to test, refine and improve their ideas.'],
                    ['icon' => 'leaf', 'title' => 'Sustainable Manufacturing (from industry perspective)', 'body' => 'Students explore how manufacturers can use materials and energy more efficiently.'],
                ]),
            ]),
            $this->write($page, $institutionStories, 'media_cards', 'institutional-impact-stories', [
                'heading' => null, 'layout' => 'tall_wide', 'card_link_label' => 'Read', 'tone' => 'white',
                'items' => $this->photoStories($institutionStories?->data['items'] ?? [], [
                    ['title' => 'Applying robotics in manufacturing', 'body' => 'Explore a case study about how a manufacturing team can test improvements on a production line.', 'image' => $this->photo('p022'), 'link_url' => '#enquiry:case-study'],
                    ['title' => 'Engineering pathways into industry', 'body' => 'See how students prepare for production and engineering roles through practical work.', 'image' => $this->photo('p102'), 'link_url' => '/placements', 'secondary_label' => 'Explore placements', 'secondary_url' => '/placements'],
                    ['title' => 'Researching sustainable production', 'body' => 'Explore approaches to energy and resource efficiency in manufacturing.', 'image' => $this->photo('p030'), 'link_url' => '/research-and-consultancy/research'],
                ]),
            ]),
            $this->write($page, $student, 'image_mosaic', 'student-impact', [
                'heading' => 'Student Impact', 'layout' => 'strip',
                'body' => $this->copy($student?->data['body'] ?? null, 'Graduate stories show how practical engineering education can open up new roles and help students turn their ideas into working prototypes.'),
                'images' => $this->images(['p128', 'p049']),
                'quote' => $this->copy($student?->data['quote'] ?? null, 'Practical projects can give graduates the confidence to bring what they have learned into a new role.'),
                'quote_name' => $this->copy($student?->data['quote_name'] ?? null, 'Sample perspective'),
                'quote_role' => $this->copy($student?->data['quote_role'] ?? null, 'Illustrative testimonial'),
            ]),
            $this->write($page, $studentStories, 'media_cards', 'student-impact-stories', [
                'heading' => null, 'layout' => 'story', 'card_link_label' => 'Read', 'tone' => 'white',
                'items' => $this->studentStories($studentStories?->data['items'] ?? [], $caseStudy),
            ]),
            $this->write($page, $logos, 'logo_strip', 'impact-partners', [
                'heading' => 'Partnerships at NAMTECH', 'layout' => 'band', 'sample_when_empty' => true,
            ]),
            $this->write($page, $social, 'card_grid', 'social-impact', [
                'heading' => 'NAMTECH’s Social Impact', 'style' => 'joined_mobile_carousel', 'columns' => '3', 'tone' => null,
                'body' => $this->copy($social?->data['body'] ?? null, 'NAMTECH’s hubs and partner ITIs help bring practical training, new equipment and industry experience to more learners.'),
                'cta_label' => 'Partner for progress', 'cta_url' => '#enquiry:partnership',
                'mobile_heading' => 'NAMTECH’s Institutional Impact', 'mobile_cta_label' => 'Partnerships at NAMTECH', 'mobile_cta_url' => '/partnerships',
                'cards' => $this->cards($social?->data['cards'] ?? $social?->data['tabs'][0]['cards'] ?? [], [
                    ['icon' => 'people', 'title' => 'Infrastructure of the hubs', 'body' => 'Hubs give learners access to equipment and technical teaching spaces.'],
                    ['icon' => 'people', 'title' => 'Spokes’ ITI Network', 'body' => 'Partner ITIs connect local learners and instructors to the hubs’ resources.'],
                    ['icon' => 'people', 'title' => 'Learning Delivery Model', 'body' => 'Projects and demonstrations help students apply new skills in a practical setting.'],
                ]),
            ]),
            $this->write($page, $socialStories, 'media_cards', 'social-impact-stories', [
                'heading' => null, 'layout' => 'story', 'card_link_label' => 'Read', 'tone' => null,
                'items' => $this->socialStories($socialStories?->data['items'] ?? [], $caseStudy),
            ]),
            $this->write($page, $voices, 'testimonials', 'impact-perspectives', [
                'heading' => "See what others\nsay About Us", 'layout' => 'spotlight', 'tone' => 'white',
                'image' => $this->photo('p099'), 'photo_caption' => 'Illustrative photo',
                'items' => $this->perspectives($voices?->data['items'] ?? []),
            ]),
            $this->write($page, $closing, 'cta_band', 'more-impact-stories', [
                'heading' => 'Explore More Impact Stories', 'tone' => 'lavender', 'cta_label' => 'View all', 'cta_url' => self::StoriesUrl,
                'body' => $this->copy($closing?->data['body'] ?? null, 'Read more about students, faculty and industry partners applying engineering skills in the classroom and beyond.'),
            ]),
        ];

        $selected = array_map(fn (PageSection $section): int => $section->id, $ordered);
        foreach ([...$ordered, ...$sections->whereNotIn('id', $selected)] as $index => $section) {
            $changes = ['sort_order' => $index];
            if (! in_array($section->id, $selected, true) && $section->type === 'mission' && ($section->data['heading'] ?? null) === 'Built to Build India - The 3 Million Mission') {
                $changes['is_visible'] = false;
            }
            $section->update($changes);
        }

        $page->syncMediaUsage();
    }

    private function prepareStories(): Post
    {
        $category = Category::query()->firstOrCreate(['slug' => 'industry-case-studies'], ['name' => 'Industry Case Studies']);
        $caseStudy = Post::query()->published()->where('type', 'story')->where('category_id', $category->id)
            ->orderByRaw('case when slug = ? then 1 else 0 end', [self::SampleCase])
            ->latest('published_at')->first();
        if (! $caseStudy) {
            $caseStudy = Post::query()->firstOrNew(['type' => 'story', 'slug' => self::SampleCase]);
            $caseStudy->fill([
                'title' => 'Sample Case Study: Improving a Manufacturing Process',
                'category_id' => $category->id,
                'excerpt' => 'An illustrative case study about identifying a production bottleneck and testing a better workflow.',
                'body' => '<p>This sample illustrates how an engineering team might study a production process and evaluate improvements.</p>',
                'image_id' => $this->photo('p139'), 'noindex' => true,
            ]);
            $caseStudy->forceFill(['status' => ContentStatus::Published, 'published_at' => now()->subMinute()])->save();
            $this->writeCaseStudy($caseStudy);
        }

        foreach (Post::query()->published()->where('type', 'story')->where('slug', '!=', self::SampleCase)->with('sections')->get() as $story) {
            if ($story->category_id === $category->id || ! $this->placeholder($story->body)) {
                continue;
            }
            if ($story->sections->contains(fn (PageSection $section): bool => $section->is_visible)) {
                continue;
            }
            $this->writeShortStory($story);
        }

        return $caseStudy;
    }

    private function writeShortStory(Post $story): void
    {
        $lead = $story->slug === self::FeaturedStory;
        $intro = $story->excerpt ?: 'This story introduces an example of how students can apply engineering knowledge to practical work.';
        $body = '<p>'.e($intro).'</p>';
        if ($lead) {
            $story->image_id = $this->photo('p139');
        }
        if (blank($story->excerpt)) {
            $story->excerpt = 'Explore a NAMTECH student impact story and the practical work behind it.';
        }

        $this->appendStorySections($story, [
            ['page_hero', 'story-hero', ['image' => $lead ? $this->photo('p039') : $story->image_id]],
            ['rich_text', 'introduction', ['heading' => 'Introduction', 'body' => $body]],
            ['rich_text', 'practical-learning', ['heading' => 'Learning through practical work', 'body' => '<p><strong>Sample context.</strong> Project work gives students an opportunity to examine an engineering problem, try an approach and evaluate the result.</p>']],
            ['image_mosaic', 'story-gallery', ['images' => $this->images(['p033', 'p050', 'p136', 'p127'])]],
            ['rich_text', 'putting-skills-to-work', ['heading' => 'Putting engineering skills to work', 'body' => '<p><strong>Sample context.</strong> Technical knowledge, clear communication and testing help teams move from an idea to a workable solution.</p>']],
            ['image_carousel', 'story-photos', ['images' => $this->images(['p114', 'p124', 'p109', 'p102'])]],
            ['rich_text', 'conclusion', ['heading' => 'Conclusion', 'body' => '<p><strong>Sample context.</strong> Practical experience gives students a way to develop their skills and apply them to new engineering challenges.</p>']],
        ]);
        $story->update(['body' => $body]);
        $story->syncMediaUsage();
    }

    private function writeCaseStudy(Post $story): void
    {
        $this->appendStorySections($story, [
            ['page_hero', 'story-hero', ['image' => $this->photo('p039')]],
            ['image_mosaic', 'case-study-challenge', [
                'heading' => 'The Challenge', 'body' => 'Sample scenario: a manufacturing team wants to understand why one stage limits production and identify a change it can test safely.',
                'images' => $this->images(['p028', 'p139']),
                'quote' => 'A useful first step is to observe the process and agree on what the team wants to improve.',
                'quote_name' => 'Sample perspective', 'quote_role' => 'Illustrative testimonial',
            ]],
            ['image_text', 'case-study-approach', [
                'heading' => 'Our Approach', 'body' => '<p>In this example, the team maps the workflow, observes the equipment in use and discusses constraints with operators before choosing an improvement to test.</p>',
                'layout' => 'panel', 'image_position' => 'left', 'image' => $this->photo('p120'),
            ]],
            ['rich_text', 'case-study-solutions', [
                'heading' => 'Our Solutions', 'toc' => true, 'body' => '<h2>Observe the workflow</h2><p>Record where work queues build up and identify potential causes with the operators.</p><h2>Test a revised process</h2><p>Run a small trial and review elapsed time, quality and the team’s observations.</p><h2>Review and improve</h2><p>Compare the trial with the original process and document what to test next.</p>',
            ]],
            ['image_mosaic', 'case-study-solution-photo', ['images' => $this->images(['p114'])]],
            ['rich_text', 'case-study-next-steps', ['heading' => 'Putting the changes into practice', 'body' => '<p>This illustrative case shows how a team can test and document a workflow improvement before adopting it more widely.</p>']],
            ['image_mosaic', 'case-study-gallery', ['images' => $this->images(['p033', 'p050', 'p136', 'p127'])]],
        ]);
        $story->syncMediaUsage();
    }

    private function appendStorySections(Post $story, array $sections): void
    {
        foreach ($sections as $order => [$type, $anchor, $data]) {
            $story->sections()->updateOrCreate(['anchor' => $anchor], [
                'type' => $type, 'data' => SectionRegistry::normalize($type, $data), 'is_visible' => true, 'sort_order' => $order,
            ]);
        }
    }

    private function studentStories(array $existing, Post $caseStudy): array
    {
        $posts = Post::query()->published()->where('type', 'story')->whereKeyNot($caseStudy->id)
            ->orderByRaw('case when slug = ? then 0 else 1 end', [self::FeaturedStory])->orderBy('id')->limit(3)->get();
        $photos = ['p124', 'p109', 'p139'];
        $items = [];
        foreach ($posts as $index => $post) {
            $items[] = [
                'title' => $post->slug === self::FeaturedStory ? 'iPTP Graduate Placed as Smart Manufacturing Engineer in Germany' : $post->title,
                'body' => $post->excerpt, 'image' => $this->photo($photos[$index]), 'link_url' => $post->publicPath(),
            ];
        }

        return $this->photoStories($existing, $items ?: [[
            'title' => 'Sample student impact story', 'body' => 'An example of a learner applying skills developed in a manufacturing lab.',
            'image' => $this->photo('p124'), 'link_url' => self::StoriesUrl,
        ]]);
    }

    private function socialStories(array $existing, Post $caseStudy): array
    {
        $lead = Post::query()->published()->where('type', 'story')->where('slug', self::FeaturedStory)->first();

        return $this->photoStories($existing, [
            [
                'title' => $lead ? 'iPTP Graduate Placed as Smart Manufacturing Engineer in Germany' : 'Sample technical education pathway',
                'body' => $lead?->excerpt ?? 'An example of how hands-on training can support learners moving into manufacturing careers.',
                'image' => $this->photo('p045'), 'link_url' => $lead?->publicPath() ?? self::StoriesUrl,
            ],
            ['title' => $caseStudy->title, 'body' => $caseStudy->excerpt, 'image' => $this->photo('p017'), 'link_url' => $caseStudy->publicPath()],
        ]);
    }

    private function perspectives(array $items): array
    {
        $published = array_values(array_filter($items, fn (array $item): bool => ! $this->placeholder($item['name'] ?? null)));
        if ($published) {
            return $published;
        }

        return [
            ['name' => 'Sample educator perspective', 'role' => 'Illustrative testimonial', 'quote' => 'Practical projects give students a chance to connect what they learn with the way engineering teams work.'],
            ['name' => 'Sample industry perspective', 'role' => 'Illustrative testimonial', 'quote' => 'Working on a real problem helps learners understand the choices and constraints that shape a production process.'],
            ['name' => 'Sample student perspective', 'role' => 'Illustrative testimonial', 'quote' => 'I learn more when I can test an idea, discuss the result and try a different approach.'],
            ['name' => 'Sample research perspective', 'role' => 'Illustrative testimonial', 'quote' => 'Collaborative projects bring together people who approach an engineering question from different fields.'],
        ];
    }

    private function cards(array $existing, array $defaults): array
    {
        return array_map(function (array $fallback, int $index) use ($existing): array {
            $item = $existing[$index] ?? [];

            return [...$fallback, ...$item, 'body' => $this->copy($item['body'] ?? null, $fallback['body'])];
        }, $defaults, array_keys($defaults)) + array_slice($existing, count($defaults), preserve_keys: true);
    }

    private function photoStories(array $existing, array $defaults): array
    {
        $result = [];
        foreach ($defaults as $index => $fallback) {
            $item = $existing[$index] ?? [];
            $result[] = [
                ...$item, ...$fallback,
                'title' => $this->copy($item['title'] ?? null, $fallback['title']),
                'body' => $this->copy($item['body'] ?? null, $fallback['body'] ?? ''),
                'secondary_label' => $item['secondary_label'] ?? $fallback['secondary_label'] ?? 'View all impact stories',
                'secondary_url' => $item['secondary_url'] ?? $fallback['secondary_url'] ?? self::StoriesUrl,
            ];
        }

        return [...$result, ...array_slice($existing, count($defaults))];
    }

    private function write(Page $page, ?PageSection $section, string $type, string $anchor, array $changes): PageSection
    {
        $section ??= $page->sections()->make();
        $section->fill([
            'type' => $type, 'anchor' => $anchor, 'is_visible' => true,
            'data' => SectionRegistry::normalize($type, [...($section->data ?? []), ...$changes]),
            'sort_order' => $section->sort_order ?? 0,
        ])->save();

        return $section;
    }

    private function copy(?string $value, string $fallback): string
    {
        return $this->placeholder($value) ? $fallback : $value;
    }

    private function placeholder(?string $value): bool
    {
        $plainText = Str::lower(trim(strip_tags((string) $value)));

        return $plainText === '' || $plainText === 'content to be confirmed.'
            || Str::startsWith($plainText, ['sample', 'lorem ipsum', 'introduction sample', 'introductionsample']);
    }

    private function images(array $photos): array
    {
        return array_map(fn (string $name): array => ['image' => $this->photo($name)], $photos);
    }

    private function photo(string $name): int
    {
        return $this->media[$name] ??= Media::query()->where('status', 'ready')
            ->whereIn('original_name', [$name.'.jpg', 'impact-'.$name.'.webp'])->value('id')
            ?? $this->uploader->storePath(
                resource_path('images/impact/'.$name.'.webp'),
                'impact-'.$name.'.webp',
                ['alt' => self::Photos[$name]],
                sync: true,
            )->id;
    }
}
