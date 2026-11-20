<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Centre;
use App\Models\Event;
use App\Models\Faculty;
use App\Models\Gallery;
use App\Models\JobOpening;
use App\Models\Leader;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Program;
use App\Models\Publication;
use App\Models\School;
use App\Sections\SampleContent;
use App\Sections\SectionPresenter;
use App\Services\PublicRenderer;
use App\Support\Presenters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SiteController extends Controller
{
    public function __construct(private PublicRenderer $renderer, private SectionPresenter $sections) {}

    public function home(): Response
    {
        $page = Page::query()->home()->published()->with('sections')->first();
        abort_if($page === null, 404);

        return $this->renderer->page($page);
    }

    public function page(string $path): Response
    {
        $page = Page::query()->published()->where('path', trim($path, '/'))->where('template', '!=', 'home')->with('sections')->firstOrFail();

        return $this->renderer->page($page);
    }

    public function programs(Request $request): Response
    {
        $level = $request->query('level');
        $school = $request->query('school');

        $programs = Program::query()->published()->with('image')
            ->when(is_string($level) && $level !== '', fn (Builder $q) => $q->where('level', $level))
            ->when(is_string($school) && $school !== '', fn (Builder $q) => $q->whereHas('school', fn (Builder $s) => $s->where('slug', $school)))
            ->orderBy('sort_order')->get();

        return $this->listing('programs', 'site/programs/Index', 'Programs', [
            'programs' => $programs->map(Presenters::programCard(...)),
            'levels' => array_intersect_key(Program::Levels, array_flip(Program::query()->published()->distinct()->pluck('level')->all())),
            'schools' => School::query()->published()->orderBy('sort_order')->pluck('name', 'slug'),
            'filters' => ['level' => $level, 'school' => $school],
        ], 'Postgraduate, executive and industry programs in advanced manufacturing, engineering and technology at NAMTECH.');
    }

    public function program(string $slug): Response|RedirectResponse
    {
        $program = Program::query()->published()->where('slug', $slug)->firstOrFail();

        if ($program->isIptp()) {
            return redirect($program->publicPath(), 301);
        }

        return $this->renderer->program($program);
    }

    public function iptpProgram(string $name): Response
    {
        return $this->renderer->program(Program::query()->published()->where('slug', Program::IptpSlugPrefix.$name)->firstOrFail());
    }

    public function schools(): Response
    {
        return $this->listing('schools', 'site/schools/Index', 'Schools & centres', [
            'schools' => School::query()->published()->with('image')->orderBy('sort_order')->get()->map(Presenters::schoolCard(...)),
            'centres' => Centre::query()->published()->with('image')->orderBy('sort_order')->get()->map(Presenters::centreCard(...)),
        ], 'NAMTECH’s MET Innovation Schools and Centres for the future of manufacturing.');
    }

    public function school(string $slug): Response
    {
        return $this->renderer->school(School::query()->published()->where('slug', $slug)->firstOrFail());
    }

    public function centre(string $slug): Response
    {
        return $this->renderer->centre(Centre::query()->published()->where('slug', $slug)->firstOrFail());
    }

    public function faculty(Request $request): Response
    {
        $search = trim((string) $request->query('q', ''));
        $school = $request->query('school');
        $type = array_key_exists((string) $request->query('type'), Faculty::Categories) ? (string) $request->query('type') : null;

        $people = Faculty::query()->published()->with('photo')
            ->when($search !== '', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
            ->when(is_string($school) && $school !== '', fn (Builder $q) => $q->whereHas('school', fn (Builder $s) => $s->where('slug', $school)))
            ->when($type, fn (Builder $q) => $q->where('category', $type))
            ->orderBy('sort_order')->orderBy('name')->paginate(24)->withQueryString()
            ->through(fn (Faculty $f) => Presenters::facultyCard($f) + ['category' => Faculty::Categories[$f->category] ?? null]);

        return $this->listing('faculty', 'site/faculty/Index', 'Faculty', [
            'people' => $people,
            'schools' => School::query()->published()->orderBy('sort_order')->pluck('name', 'slug'),
            'types' => Faculty::Categories,
            'filters' => ['q' => $search, 'school' => $school, 'type' => $type],
        ], 'Meet NAMTECH’s faculty of academicians, industry leaders and global experts.');
    }

    public function facultyProfile(string $slug): Response
    {
        return $this->renderer->faculty(Faculty::query()->published()->where('slug', $slug)->firstOrFail());
    }

    public function posts(Request $request, string $type): Response
    {
        $category = $request->query('category');
        $posts = Post::query()->published()->with('image', 'category')->where('type', $type)
            ->when(is_string($category) && $category !== '', fn (Builder $q) => $q->whereHas('category', fn (Builder $c) => $c->where('slug', $category)))
            ->latest('published_at')->paginate(12)->withQueryString()
            ->through(Presenters::postCard(...));

        $title = $type === 'blog' ? 'Blogs' : (Post::Types[$type]['label'] ?? 'News');

        return $this->listing(Post::prefixFor($type), 'site/posts/Index', $title, [
            'posts' => $posts,
            'type' => $type,
            'categories' => Category::query()->whereHas('posts', fn (Builder $q) => $q->published()->where('type', $type))->orderBy('name')->pluck('name', 'slug'),
            'filters' => ['category' => $category],
        ], 'The latest '.strtolower($title).' from NAMTECH.');
    }

    public function post(string $slug, string $type): Response|RedirectResponse
    {
        $post = Post::query()->published()->where('type', $type)->where('slug', $slug)->firstOrFail();

        return $post->externalLink() ? redirect()->away($post->externalLink()) : $this->renderer->post($post);
    }

    public function gallery(string $slug): Response
    {
        return $this->renderer->gallery(Gallery::query()->published()->where('slug', $slug)->firstOrFail());
    }

    public function partnerStory(string $slug): Response
    {
        return $this->renderer->partner(Partner::query()->published()->where('slug', $slug)->whereNotNull('story')->firstOrFail());
    }

    public function media(Request $request): Response
    {
        $tabs = ['news' => 'News', 'press' => 'Press Release', 'announcement' => 'Announcements'];
        $tab = array_key_exists((string) $request->query('tab'), $tabs) ? (string) $request->query('tab') : 'news';
        $categories = array_values(array_filter((array) $request->query('categories', []), 'is_string'));
        $years = array_values(array_filter(array_map('intval', (array) $request->query('years', []))));

        $query = Post::query()->published()->with('image', 'category')->where('type', $tab)
            ->when($categories, fn (Builder $q) => $q->whereHas('category', fn (Builder $c) => $c->whereIn('slug', $categories)))
            ->when($years, fn (Builder $q) => $q->where(fn (Builder $q) => collect($years)->each(fn (int $y) => $q->orWhereYear('published_at', $y))));

        $featured = $tab === 'news' && $request->query('page', 1) == 1 ? (clone $query)->orderByDesc('is_featured')->latest('published_at')->first() : null;
        $posts = $query->when($featured, fn (Builder $q) => $q->whereKeyNot($featured->id))->latest('published_at')
            ->paginate(10)->withQueryString()->through(Presenters::postCard(...));

        return $this->listing('media', 'site/posts/Media', 'NAMTECH in the Media', [
            'tabs' => $tabs,
            'tab' => $tab,
            'featured' => $featured ? Presenters::postCard($featured) : null,
            'posts' => $posts,
            'sample_image' => (new SampleContent)->image(),
            'categories' => Category::query()->whereHas('posts', fn (Builder $q) => $q->published()->where('type', 'news'))->orderBy('name')->pluck('name', 'slug'),
            'years' => Post::query()->published()->where('type', $tab)->selectRaw('distinct '.$this->yearExpression().' as y')->orderByDesc('y')->pluck('y')->map(fn ($y) => (int) $y)->filter()->values(),
            'filters' => ['categories' => $categories, 'years' => $years],
        ], 'News, press releases and announcements from NAMTECH.');
    }

    public function stories(Request $request): Response
    {
        $journey = 'student-journey-innovations';
        $designed = [
            'industry-case-studies' => 'Industry Case Studies',
            'industrial-social-impact' => 'Industrial & Social Impact',
            $journey => 'Student Journey & Innovations',
            'faculty-stories' => 'Faculty Stories',
            'research-breakthroughs' => 'Research Breakthroughs',
        ];
        $slugs = array_keys($designed);
        $categoryNames = Category::query()->whereIn('slug', $slugs)->pluck('name', 'slug')->all();
        $categories = array_replace($designed, $categoryNames) + Category::query()->whereNotIn('slug', $slugs)
            ->whereHas('posts', fn (Builder $q) => $q->published()->where('type', 'story'))->orderBy('id')->pluck('name', 'slug')->all();
        $category = $request->query('category');
        $query = Post::query()->published()->with('image', 'category')->where('type', 'story');

        if (! $request->query->has('category') && $request->query('view') !== 'all'
            && (clone $query)->whereHas('category', fn (Builder $c) => $c->where('slug', $journey))->exists()) {
            $category = $journey;
        }

        $category = is_string($category) && $category !== '' ? $category : null;
        $query->when($category, fn (Builder $q) => $q->whereHas('category', fn (Builder $c) => $c->where('slug', $category)));
        $featured = (clone $query)->orderByDesc('is_featured')->latest('published_at')->latest('id')->first();
        $posts = $query->when($featured, fn (Builder $q) => $q->whereKeyNot($featured->id))->latest('published_at')->latest('id')
            ->paginate(9)->withQueryString()->through(Presenters::postCard(...));

        return $this->listing('impact/stories', 'site/posts/Stories', 'Impact Stories', [
            'categories' => $categories,
            'featured' => $featured && $posts->onFirstPage() ? Presenters::postCard($featured) : null,
            'posts' => $posts,
            'filters' => ['category' => $category],
            'sample_image' => $featured ? null : (new SampleContent)->image(),
        ], 'Case studies and stories of impact from NAMTECH students, faculty and partners.');
    }

    public function blogs(Request $request): Response
    {
        $search = trim((string) $request->query('q', ''));
        $topic = $request->query('topic');
        $sort = $request->query('sort') === 'oldest' ? 'oldest' : 'recent';

        $posts = Post::query()->published()->with('image', 'category')->where('type', 'blog')
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $q) => $q->where('title', 'like', "%{$search}%")->orWhere('excerpt', 'like', "%{$search}%")))
            ->when(is_string($topic) && $topic !== '', fn (Builder $q) => $q->whereHas('category', fn (Builder $c) => $c->where('slug', $topic)))
            ->orderBy('published_at', $sort === 'oldest' ? 'asc' : 'desc')
            ->paginate(9)->withQueryString()->through(Presenters::postCard(...));

        $featured = Post::query()->published()->with('image', 'category')->where('type', 'blog')->where('is_featured', true)
            ->latest('published_at')->limit(4)->get();
        if ($featured->count() < 4) {
            $featured = $featured->concat(Post::query()->published()->with('image', 'category')->where('type', 'blog')
                ->whereNotIn('id', $featured->pluck('id'))->latest('published_at')->limit(4 - $featured->count())->get());
        }

        return $this->listing('blogs', 'site/posts/Blogs', 'Voices of NAMTECH', [
            'featured' => $featured->map(Presenters::postCard(...))->values(),
            'posts' => $posts,
            'topics' => Category::query()->whereHas('posts', fn (Builder $q) => $q->published()->where('type', 'blog'))->orderBy('name')->pluck('name', 'slug'),
            'filters' => ['q' => $search, 'topic' => $topic, 'sort' => $sort],
        ], 'Blogs and perspectives from the NAMTECH community.');
    }

    public function events(Request $request): Response
    {
        $categories = Event::Categories;
        $category = array_key_exists((string) $request->query('category'), $categories) ? (string) $request->query('category') : array_key_first($categories);
        $units = array_values(array_filter((array) $request->query('units', []), 'is_string'));
        $from = $request->date('from');
        $to = $request->date('to');

        $events = Event::query()->published()->with('image')->where('category', $category)
            ->when($units, fn (Builder $q) => $q->where(fn (Builder $q) => $q
                ->whereHas('school', fn (Builder $s) => $s->whereIn('slug', $units))
                ->orWhereHas('centre', fn (Builder $c) => $c->whereIn('slug', $units))))
            ->when($from, fn (Builder $q) => $q->where('starts_at', '>=', $from->startOfDay()))
            ->when($to, fn (Builder $q) => $q->where('starts_at', '<=', $to->endOfDay()))
            ->orderBy('starts_at')->paginate(10)->withQueryString()->through(Presenters::eventCard(...));

        return $this->listing('events', 'site/events/Index', 'Events', [
            'categories' => $categories,
            'category' => $category,
            'events' => $events,
            'units' => School::query()->published()->orderBy('sort_order')->pluck('name', 'slug')
                ->union(Centre::query()->published()->orderBy('sort_order')->pluck('name', 'slug')),
            'filters' => ['units' => $units, 'from' => $from?->toDateString(), 'to' => $to?->toDateString()],
        ], 'Conferences, lectures, masterclasses and outreach programmes at NAMTECH.');
    }

    public function event(string $slug): Response
    {
        return $this->renderer->event(Event::query()->published()->where('slug', $slug)->firstOrFail());
    }

    public function publications(Request $request): Response
    {
        $school = $request->query('school');
        $topic = $request->query('topic');
        $sort = $request->query('sort') === 'oldest' ? 'oldest' : 'recent';

        $publications = Publication::query()->published()->with('image', 'school')
            ->when(is_string($school) && $school !== '', fn (Builder $q) => $q->whereHas('school', fn (Builder $s) => $s->where('slug', $school)))
            ->when(is_string($topic) && $topic !== '', fn (Builder $q) => $q->where('topic', $topic))
            ->orderBy('published_on', $sort === 'oldest' ? 'asc' : 'desc')
            ->paginate(10)->withQueryString()->through(Presenters::publicationCard(...));

        return $this->listing('research-and-consultancy/publications', 'site/research/Index', 'Research and Publications', [
            'publications' => $publications,
            'schools' => School::query()->published()->orderBy('sort_order')->pluck('name', 'slug'),
            'topics' => Publication::query()->published()->whereNotNull('topic')->distinct()->orderBy('topic')->pluck('topic'),
            'filters' => ['school' => $school, 'topic' => $topic, 'sort' => $sort],
        ], 'Research papers and publications by NAMTECH faculty and researchers.');
    }

    public function publication(string $slug): Response
    {
        return $this->renderer->publication(Publication::query()->published()->where('slug', $slug)->firstOrFail());
    }

    public function leader(string $slug): Response
    {
        return $this->renderer->leader(Leader::query()->published()->where('slug', $slug)->firstOrFail());
    }

    public function job(string $slug): Response
    {
        return $this->renderer->job(JobOpening::query()->published()->where('slug', $slug)->firstOrFail());
    }

    private function yearExpression(): string
    {
        return DB::connection()->getDriverName() === 'sqlite' ? "strftime('%Y', published_at)" : 'YEAR(published_at)';
    }

    private function listing(string $path, string $component, string $title, array $props, string $description): Response
    {
        $page = Page::query()->published()->where('path', $path)->with('sections')->first();
        $seo = $page ? $page->seo(['description' => $page->excerpt ?? $description]) : ['title' => $title, 'description' => $description, 'canonical' => url($path)];

        return $this->renderer->respond($component, $props + [
            'title' => $page?->title ?? $title,
            'sections' => $page ? $this->sections->present($page->sections) : [],
            'breadcrumbs' => [['label' => $page?->title ?? $title, 'url' => '/'.$path]],
        ], $seo);
    }
}
