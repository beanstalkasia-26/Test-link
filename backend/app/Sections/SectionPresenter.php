<?php

namespace App\Sections;

use App\Models\Centre;
use App\Models\Event;
use App\Models\Faculty;
use App\Models\Gallery;
use App\Models\Initiative;
use App\Models\JobOpening;
use App\Models\Leader;
use App\Models\Media;
use App\Models\PageSection;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Program;
use App\Models\Publication;
use App\Models\School;
use App\Models\Statistic;
use App\Support\Fields;
use App\Support\HtmlSanitizer;
use App\Support\Presenters;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SectionPresenter
{
    private Collection $media;

    public function present(iterable $sections): array
    {
        $sections = collect($sections)->filter(fn (PageSection $section) => $section->is_visible && SectionRegistry::exists($section->type))->values();

        $ids = $sections->flatMap(fn (PageSection $section) => SectionRegistry::mediaIds($section->type, $section->data ?? []))->unique();
        $this->media = Media::query()->whereIn('id', $ids)->get()->keyBy('id');
        $samples = new SampleContent;

        return $sections->map(fn (PageSection $section): array => [
            'id' => $section->id,
            'type' => $section->type,
            'anchor' => $section->anchor,
            'data' => $samples->fill($section->type, SectionRegistry::fields($section->type), $this->resolve($section->type, $this->withImages(
                SectionRegistry::fields($section->type),
                Fields::withoutHiddenItems(SectionRegistry::fields($section->type), $section->data ?? []),
            ))),
        ])->all();
    }

    private function withImages(array $fields, array $data): array
    {
        foreach ($fields as $field) {
            $name = $field['name'];
            if ($field['type'] === 'image') {
                $data[$name] = isset($data[$name]) ? $this->media->get((int) $data[$name])?->toImage() : null;
            }
            if ($field['type'] === 'file') {
                $document = isset($data[$name]) ? $this->media->get((int) $data[$name]) : null;
                $data[$name] = $document?->isDocument() ? ['url' => $document->documentUrl(), 'title' => $document->alt ?: $document->original_name, 'size' => $document->size] : null;
            }
            if ($field['type'] === 'repeater') {
                $data[$name] = array_map(fn ($item) => $this->withImages($field['fields'], is_array($item) ? $item : []), $data[$name] ?? []);
            }
        }

        return $data;
    }

    private function resolve(string $type, array $data): array
    {
        return match ($type) {
            'academics_tabs' => array_merge($data, [
                'schools' => School::query()->published()->with('image')->orderBy('sort_order')->get()->map(Presenters::schoolCard(...))->all(),
                'centres' => Centre::query()->published()->with('image')->orderBy('sort_order')->get()->map(Presenters::centreCard(...))->all(),
                'programs' => Program::query()->published()->with('image')
                    ->when($data['featured_only'] ?? false, fn ($q) => $q->where('is_featured', true))
                    ->orderBy('sort_order')->limit(12)->get()->map(Presenters::programCard(...))->all(),
                'program_groups' => collect(['undergraduate' => 'UG Programs', 'postgraduate' => 'PG Programs', 'executive' => 'Executive Education', 'technical' => 'iPTP'])
                    ->map(fn (string $label, string $level) => [
                        'key' => $level,
                        'label' => $label,
                        'programs' => Program::query()->published()->with('image')->where('level', $level)
                            ->when($data['featured_only'] ?? false, fn ($q) => $q->where('is_featured', true))
                            ->orderBy('sort_order')->get()->map(Presenters::programCard(...))->all(),
                    ])->filter(fn (array $group) => $group['programs'] !== [])->values()->all(),
            ]),
            'program_levels' => array_merge($data, [
                'items' => array_map(fn (array $item) => $item + [
                    'programs' => Program::query()->published()->where('level', $item['level'] ?? '')->orderBy('sort_order')
                        ->get(['id', 'title', 'slug'])->map(fn (Program $p) => ['title' => $p->title, 'url' => $p->publicPath()])->all(),
                ], $data['items'] ?? []),
            ]),
            'partners_showcase' => array_merge($data, [
                'logos' => $this->partners($data['category'] ?? null)->filter(fn (Partner $p) => $p->logo)->map(Presenters::partnerLogo(...))->values()->all(),
                'stories' => $this->partnerStories($data),
            ]),
            'logo_strip' => array_merge($data, [
                'logos' => $this->partnerLogos($data),
            ]),
            'faculty_showcase' => array_merge($data, [
                'people' => $this->featuredFaculty($data['school'] ?? null)->map(fn (Faculty $f) => Presenters::facultyCard($f) + ['category' => $f->category])->all(),
            ]),
            'people' => array_merge($data, [
                'people' => $this->peopleFor($data)
                    ->map(fn (Faculty $f) => Presenters::facultyCard($f) + ['category' => $f->category])->all(),
            ]),
            'campus_showcase', 'gallery' => array_merge($data, [
                'gallery' => ($gallery = $this->gallery($data['gallery'] ?? null)) ? Presenters::gallery($gallery) : null,
            ]),
            'mission' => array_merge($data, [
                'initiatives' => Initiative::query()->where('is_active', true)->orderBy('sort_order')->limit($data['limit'] ?? 6)->get(['id', 'title', 'body', 'icon'])->toArray(),
                'statistics' => Statistic::approvedFor($data['statistics_group'] ?? null),
            ]),
            'page_hero', 'stats_band' => array_merge($data, [
                'statistics' => Statistic::approvedFor($data['statistics_group'] ?? null),
            ]),
            'news_carousel', 'post_list' => array_merge($data, [
                'posts' => $this->posts($data),
            ]),
            'programs_list' => array_merge($data, [
                'programs' => Program::query()->published()->with('image')
                    ->when($data['level'] ?? null, fn ($q, $level) => $q->where('level', $level))
                    ->when($data['school'] ?? null, fn ($q, $school) => $q->whereHas('school', fn ($s) => $s->where('slug', $school)))
                    ->orderBy('sort_order')->get()->map(Presenters::programCard(...))->all(),
            ]),
            'leaders_grid' => array_merge($data, [
                'people' => Leader::query()->published()->with('photo')->where('group', $data['group'] ?? 'board')
                    ->orderBy('sort_order')->get()->map(Presenters::leaderCard(...))->all(),
            ]),
            'programs_tabs' => array_merge($data, [
                'levels' => ($data['tab_by'] ?? null) === 'program' ? $this->programTabs($data) : collect(Program::Levels)->map(fn (string $label, string $level) => [
                    'key' => $level,
                    'label' => $label,
                    'programs' => Program::query()->published()->with('image')->where('level', $level)
                        ->when($data['school'] ?? null, fn ($q, $school) => $q->whereHas('school', fn ($s) => $s->where('slug', $school)))
                        ->orderBy('sort_order')->get()->map(Presenters::programCard(...))->all(),
                ])->filter(fn (array $tab) => $tab['programs'] !== [])->values()->all(),
            ]),
            'events_carousel' => array_merge($data, [
                'events' => Event::query()->published()->with('image')->where('starts_at', '>=', now()->startOfDay())
                    ->when($data['category'] ?? null, fn ($q, $category) => $q->where('category', $category))
                    ->when($data['school'] ?? null, fn ($q, $school) => $q->whereHas('school', fn ($s) => $s->where('slug', $school)))
                    ->orderBy('starts_at')->limit(10)->get()->map(Presenters::eventCard(...))->all(),
            ]),
            'publications_carousel' => array_merge($data, [
                'publications' => Publication::query()->published()->with('image', 'school')
                    ->when($data['school'] ?? null, fn ($q, $school) => $q->whereHas('school', fn ($s) => $s->where('slug', $school)))
                    ->latest('published_on')->limit(8)->get()->map(Presenters::publicationCard(...))->all(),
            ]),
            'program_finder' => array_merge($data, [
                'schools' => School::query()->published()->orderBy('sort_order')->get(['id', 'name'])->toArray(),
                'programs' => Program::query()->published()->with('image')
                    ->when($data['level'] ?? null, fn ($q, $level) => $q->where('level', $level))
                    ->orderBy('sort_order')->get()->map(fn (Program $p) => Presenters::programCard($p) + ['school_id' => $p->school_id, 'apply_url' => $p->apply_url])->all(),
            ]),
            'job_listings' => array_merge($data, [
                'categories' => JobOpening::Categories,
                'jobs' => JobOpening::query()->published()->where(fn ($q) => $q->whereNull('closes_on')->orWhere('closes_on', '>=', now()->toDateString()))
                    ->orderBy('sort_order')->orderBy('closes_on')->get()->map(Presenters::jobCard(...))->all(),
            ]),
            'placement_trends' => array_merge($data, [
                'logos' => ($data['show_logos'] ?? false) ? $this->partners(null)->filter(fn (Partner $p) => $p->logo)->take(6)->map(Presenters::partnerLogo(...))->values()->all() : [],
            ]),
            'rich_text' => ($data['toc'] ?? false)
                ? (fn (array $anchored) => array_merge($data, ['body' => $anchored['html'], 'toc_items' => $anchored['toc']]))(HtmlSanitizer::withHeadingAnchors($data['body'] ?? null))
                : $data,
            'enquiry_form' => array_merge($data, [
                'programs' => ($data['form_type'] ?? 'contact') === 'program'
                    ? Program::query()->published()->orderBy('title')->get(['id', 'title'])->toArray()
                    : [],
            ]),
            default => $data,
        };
    }

    private function partners(?string $category): Collection
    {
        return Partner::query()->published()->with('logo', 'image')
            ->when($category, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $category)))
            ->orderBy('sort_order')->get();
    }

    private function partnerStories(array $data): array
    {
        if (! ($data['show_stories'] ?? false)) {
            return [];
        }

        $covers = collect($data['story_images'] ?? [])->filter(fn (array $item): bool => ! empty($item['image']))->values();
        $preferredSlugs = $covers->pluck('slug');
        $stories = $this->partners($data['category'] ?? null)
            ->filter(fn (Partner $partner): bool => $preferredSlugs->contains($partner->slug)
                || ($partner->is_featured && ($partner->image || ($data['stories_layout'] ?? null) === 'text')))
            ->sortBy(fn (Partner $partner): int => $preferredSlugs->search($partner->slug, strict: true) !== false
                ? (int) $preferredSlugs->search($partner->slug, strict: true) : $preferredSlugs->count())
            ->map(function (Partner $partner) use ($covers): array {
                $card = Presenters::partnerCard($partner);
                $cover = $covers->firstWhere('slug', $partner->slug);
                if ($cover) {
                    $card['image'] = $cover['image'];
                    $card['summary'] ??= Str::limit(strip_tags((string) $partner->story), 290);
                }

                return $card;
            })->values()->all();

        if (! $stories && ! empty($data['sample_image'])) {
            $stories[] = [
                'id' => 'sample-partner-story', 'name' => 'Sample industry collaboration',
                'summary' => 'An example of how an industry partner could work with faculty to design a hands-on manufacturing project.',
                'image' => $data['sample_image'],
            ];
        }

        return $stories;
    }

    private function partnerLogos(array $data): array
    {
        $custom = collect($data['custom_logos'] ?? [])->filter(fn (array $item): bool => ! empty($item['logo']))->values();
        $logos = $custom->isNotEmpty()
            ? $custom->map(fn (array $item, int $i): array => ['id' => 'logo-'.$i, 'name' => $item['name'] ?? '', 'url' => null, 'logo' => $item['logo']])->all()
            : $this->partners($data['category'] ?? null)->filter(fn (Partner $p) => $p->logo)->map(Presenters::partnerLogo(...))->values()->all();

        if (! $logos && ($data['sample_when_empty'] ?? false)) {
            $logos[] = ['id' => 'sample-partner', 'name' => 'Sample partner', 'logo' => null];
        }

        return $logos;
    }

    private function posts(array $data): array
    {
        $posts = Post::query()->published()->with('image', 'category')
            ->when($data['post_type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($data['category'] ?? null, fn ($q, $category) => $q->whereHas('category', fn ($c) => $c->where('slug', $category)))
            ->latest('published_at')->limit($data['limit'] ?? 6)->get()->map(Presenters::postCard(...))->all();

        if (! $posts && ! empty($data['sample_image'])) {
            $posts[] = [
                'id' => 'sample-insight', 'title' => 'How industry projects can support engineering education',
                'type' => 'Sample insight', 'image' => $data['sample_image'],
                'excerpt' => 'An example article about bringing practical manufacturing challenges into the classroom.',
            ];
        }

        return $posts;
    }

    private function programTabs(array $data): array
    {
        $cards = Program::query()->published()->with('image')
            ->when($data['school'] ?? null, fn ($q, $school) => $q->whereHas('school', fn ($s) => $s->where('slug', $school)))
            ->orderBy('sort_order')->get()->map(Presenters::programCard(...))->values();

        return $cards->map(fn (array $card, int $i) => [
            'key' => 'program-'.$card['id'],
            'label' => preg_match('/\(([^)]+)\)\s*:?\s*(.+)$/', $card['title'], $m) ? $m[1].' '.$m[2] : $card['title'],
            'programs' => $cards->slice($i)->concat($cards->slice(0, $i))->values()->all(),
        ])->all();
    }

    private function peopleFor(array $data): Collection
    {
        $slugs = collect($data['members'] ?? [])->pluck('faculty')->filter()->values();
        if ($slugs->isEmpty()) {
            return $this->faculty($data['school'] ?? null, $data['limit'] ?? 24);
        }

        return Faculty::query()->published()->with('photo')->whereIn('slug', $slugs)->get()
            ->sortBy(fn (Faculty $faculty) => $slugs->search($faculty->slug))->values();
    }

    private function faculty(?string $school, int $limit, bool $featuredFirst = false): Collection
    {
        return Faculty::query()->published()->with('photo')
            ->when($school, fn ($q) => $q->whereHas('school', fn ($s) => $s->where('slug', $school)))
            ->when($featuredFirst, fn ($q) => $q->orderByDesc('is_featured'))
            ->orderBy('sort_order')->limit($limit)->get();
    }

    private function featuredFaculty(?string $school): Collection
    {
        $featured = Faculty::query()->published()->with('photo')->where('is_featured', true)
            ->when($school, fn ($q) => $q->whereHas('school', fn ($s) => $s->where('slug', $school)))
            ->orderBy('sort_order')->get();

        return $featured->isNotEmpty() ? $featured : $this->faculty($school, 12);
    }

    private function gallery(?string $slug): ?Gallery
    {
        return $slug ? Gallery::query()->published()->with('items.media', 'cover')->where('slug', $slug)->first() : null;
    }
}
