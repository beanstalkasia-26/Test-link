<?php

namespace Tests\Feature;

use App\Models\Centre;
use App\Models\Faculty;
use App\Models\Gallery;
use App\Models\Page;
use App\Models\Post;
use App\Models\Program;
use App\Models\Redirect;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\PageAssert as Assert;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_its_published_sections_with_seo_metadata(): void
    {
        $this->page('Home', 'home', [
            ['home_hero', ['badges' => [['text' => 'Make in India']], 'slides' => [['title' => 'Powering India’s Next Manufacturing Revolution', 'body' => 'Intro', 'image' => null]]]],
            ['news_carousel', ['heading' => 'News & Insights', 'limit' => 4]],
        ], template: 'home');

        $this->get('/')
            ->assertOk()
            ->assertJsonPath('props.seo.title', 'NAMTECH – Institute of Manufacturing Innovation')
            ->assertPage(fn (Assert $page) => $page
                ->component('site/Page')
                ->where('sections.0.type', 'home_hero')
                ->where('sections.0.data.slides.0.title', 'Powering India’s Next Manufacturing Revolution')
                ->where('seo.canonical', url('/'))
                ->has('seo.jsonLd', 1));
        $this->get('/')->assertOk()->assertJsonStructure(['props' => ['site' => ['settings', 'announcements', 'menus']]]);
    }

    public function test_draft_and_unpublished_pages_are_not_public(): void
    {
        $this->page('Draft page', 'draft-page', live: false);
        $unpublished = $this->page('Old page', 'old-page');
        $unpublished->update(['status' => 'unpublished']);

        $this->get('/draft-page')->assertNotFound()->assertPage(fn (Assert $page) => $page->component('Error')->where('status', 404));
        $this->get('/old-page')->assertNotFound();
    }

    public function test_nested_pages_resolve_by_full_path(): void
    {
        $parent = $this->page('Partnerships', 'partnerships');
        $child = Page::create(['title' => 'Academic', 'slug' => 'academic', 'parent_id' => $parent->id, 'template' => 'default']);
        $this->publish($child);

        $this->get('/partnerships/academic')->assertOk()->assertPage(fn (Assert $page) => $page->where('page.title', 'Academic')->has('page.breadcrumbs', 2));
    }

    public function test_changing_a_published_slug_creates_a_permanent_redirect(): void
    {
        $page = $this->page('About', 'about-us');
        $page->update(['slug' => 'about']);

        $this->assertDatabaseHas('redirects', ['from_path' => '/about-us', 'to_url' => '/about', 'status_code' => 301]);
        $this->get('/about-us')->assertStatus(301)->assertRedirect('/about');
        $this->get('/about')->assertOk();

        $page->update(['slug' => 'about-us']);
        $this->assertDatabaseMissing('redirects', ['from_path' => '/about-us']);
        $this->assertSame('/about-us', Redirect::query()->where('from_path', '/about')->value('to_url'));
    }

    public function test_program_listing_and_detail_only_show_published_programs(): void
    {
        $live = $this->publish(Program::create(['title' => 'B.Tech in Robotics and Automation', 'slug' => 'btech-robotics', 'level' => 'undergraduate']));
        Program::create(['title' => 'Secret program', 'slug' => 'secret', 'level' => 'postgraduate']);

        $this->get('/programs')->assertOk()->assertPage(fn (Assert $page) => $page->component('site/programs/Index')->has('programs', 1)->where('programs.0.title', $live->title));
        $this->get('/programs/btech-robotics')->assertOk()->assertPage(fn (Assert $page) => $page->component('site/programs/Show')->where('seo.jsonLd.1.@type', 'Course'));
        $this->get('/programs/secret')->assertNotFound();
        $this->get('/programs?level=postgraduate')->assertPage(fn (Assert $page) => $page->has('programs', 0));
    }

    public function test_school_centre_faculty_and_gallery_pages_render(): void
    {
        $school = $this->publish(School::create(['name' => 'School of Robotics', 'slug' => 'school-of-robotics']));
        $this->publish(Centre::create(['name' => 'Center of Management', 'slug' => 'center-of-management', 'school_id' => $school->id]));
        $this->publish(Faculty::create(['name' => 'Dr. Test', 'slug' => 'dr-test', 'school_id' => $school->id, 'qualifications' => [['title' => 'PhD']]]));
        $this->publish(Gallery::create(['title' => 'Campus', 'slug' => 'campus']));

        $this->get('/schools')->assertOk()->assertPage(fn (Assert $p) => $p->component('site/schools/Index')->has('schools', 1)->has('centres', 1));
        $this->get('/schools/school-of-robotics')->assertOk()->assertPage(fn (Assert $p) => $p->where('unit.title', 'School of Robotics')->has('faculty', 1));
        $this->get('/centres/center-of-management')->assertOk()->assertPage(fn (Assert $p) => $p->where('unit.kind', 'Centre'));
        $this->get('/faculty')->assertOk()->assertPage(fn (Assert $p) => $p->component('site/faculty/Index')->where('people.total', 1));
        $this->get('/faculty/dr-test')->assertOk()->assertPage(fn (Assert $p) => $p->where('person.name', 'Dr. Test')->where('seo.jsonLd.1.@type', 'Person'));
        $this->get('/campus/galleries/campus')->assertOk()->assertPage(fn (Assert $p) => $p->component('site/Gallery'));
    }

    public function test_news_articles_have_article_metadata(): void
    {
        $post = $this->publish(Post::create(['type' => 'news', 'title' => 'Campus opens', 'slug' => 'campus-opens', 'body' => '<p>Hello</p>']));

        $this->get('/news/campus-opens')->assertOk()->assertPage(fn (Assert $page) => $page
            ->component('site/posts/Show')
            ->where('post.title', 'Campus opens')
            ->where('seo.type', 'article')
            ->where('seo.jsonLd.1.@type', 'NewsArticle'));
        $this->get('/blogs/campus-opens')->assertNotFound();
        $this->assertSame('/news/campus-opens', $post->publicPath());
    }

    public function test_search_finds_published_content_only(): void
    {
        $this->publish(Program::create(['title' => 'Master in Data Analytics & AI', 'slug' => 'data', 'level' => 'postgraduate']));
        Program::create(['title' => 'Data draft', 'slug' => 'data-draft', 'level' => 'postgraduate']);

        $this->get('/search?q=data')->assertOk()->assertPage(fn (Assert $page) => $page->has('results', 1)->where('seo.noindex', true));
    }

    public function test_sitemap_lists_indexable_published_urls(): void
    {
        $this->page('About', 'about');
        $hidden = $this->page('Hidden', 'hidden');
        $hidden->update(['noindex' => true]);
        $this->page('Draft', 'draft', live: false);

        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(url('/about'), false)
            ->assertDontSee(url('/hidden'), false)
            ->assertDontSee(url('/draft'), false);
    }

    public function test_robots_blocks_non_production_environments(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');
    }

    public function test_security_headers_are_sent(): void
    {
        $this->page('About', 'about');

        $this->get('/about')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_anonymous_pages_are_cached_and_invalidated_when_content_changes(): void
    {
        $page = $this->page('About', 'about');

        $this->get('/about')->assertHeader('X-Page-Cache', 'MISS');
        $this->get('/about')->assertHeader('X-Page-Cache', 'HIT');

        $page->update(['title' => 'About NAMTECH']);

        $this->get('/about')->assertHeader('X-Page-Cache', 'MISS')->assertPage(fn (Assert $p) => $p->where('page.title', 'About NAMTECH'));
    }

    public function test_admin_responses_are_never_cached(): void
    {
        $this->actingAs($this->admin())->get('/admin')->assertOk()->assertHeaderMissing('X-Page-Cache')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->assertStringContainsString('no-store', (string) $this->actingAs($this->admin())->get('/admin')->headers->get('Cache-Control'));
    }
}
