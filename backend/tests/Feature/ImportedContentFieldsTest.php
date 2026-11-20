<?php

namespace Tests\Feature;

use App\Models\Faculty;
use App\Models\JobOpening;
use App\Models\PageSection;
use App\Models\Post;
use App\Sections\SectionPresenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\PageAssert as Assert;
use Tests\TestCase;

class ImportedContentFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_press_coverage_with_a_link_opens_that_link_from_the_media_page(): void
    {
        $this->press('With link', ['external_url' => 'https://example.com/story']);
        $this->press('With article', ['body' => '<p>Full text</p>']);

        $this->get('/media?tab=press')->assertOk()->assertPage(fn (Assert $page) => $page
            ->where('posts.data.0.url', 'https://example.com/story')
            ->where('posts.data.0.external', true)
            ->where('posts.data.1.url', '/media/press/with-article')
            ->where('posts.data.1.external', false));

        $this->get('/media/press/with-link')->assertRedirect('https://example.com/story');
        $this->get('/media/press/with-article')->assertOk();
    }

    public function test_press_coverage_with_a_link_is_left_out_of_the_sitemap(): void
    {
        $this->press('With link', ['external_url' => 'https://example.com/story']);
        $this->press('With article', ['body' => '<p>Full text</p>']);

        $this->get('/sitemap.xml')->assertOk()
            ->assertSee('/media/press/with-article')
            ->assertDontSee('/media/press/with-link');
    }

    public function test_faculty_profile_shows_publications(): void
    {
        Faculty::create(['name' => 'Dr. Example', 'slug' => 'dr-example', 'category' => 'residential', 'publications' => '<ol><li>A paper</li></ol>', 'status' => 'published', 'published_at' => now()->subDay()]);

        $this->get('/faculty/dr-example')->assertOk()->assertPage(fn (Assert $page) => $page
            ->where('person.publications', '<ol><li>A paper</li></ol>'));
    }

    public function test_faculty_can_be_filtered_by_type(): void
    {
        foreach (['residential', 'visiting'] as $type) {
            Faculty::create(['name' => ucfirst($type), 'slug' => $type, 'category' => $type, 'status' => 'published', 'published_at' => now()->subDay()]);
        }

        $this->get('/faculty?type=visiting')->assertOk()->assertPage(fn (Assert $page) => $page
            ->has('people.data', 1)
            ->where('people.data.0.name', 'Visiting'));
        $this->get('/faculty?type=other')->assertOk()->assertPage(fn (Assert $page) => $page->has('people.data', 2));
    }

    public function test_cms_saves_the_press_link_and_faculty_publications(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/content/posts', [
            'title' => 'Press item', 'slug' => 'press-item', 'type' => 'press', 'tags' => [], 'external_url' => 'https://example.com/story',
        ])->assertSessionHasNoErrors();
        $this->assertSame('https://example.com/story', Post::query()->where('slug', 'press-item')->value('external_url'));

        $this->actingAs($admin)->post('/admin/content/faculty', [
            'name' => 'Dr. Example', 'slug' => 'dr-example', 'category' => 'visiting', 'programs' => [], 'publications' => '<ol><li>A paper</li></ol>',
        ])->assertSessionHasNoErrors();
        $this->assertStringContainsString('A paper', (string) Faculty::query()->where('slug', 'dr-example')->value('publications'));
    }

    public function test_people_section_lists_chosen_faculty_in_the_chosen_order(): void
    {
        foreach (['first', 'second', 'not-chosen'] as $i => $slug) {
            Faculty::create(['name' => $slug, 'slug' => $slug, 'category' => 'residential', 'sort_order' => $i, 'status' => 'published', 'published_at' => now()->subDay()]);
        }
        $section = new PageSection(['type' => 'people', 'is_visible' => true, 'data' => ['members' => [['faculty' => 'second'], ['faculty' => 'first']]]]);

        $people = app(SectionPresenter::class)->present([$section])[0]['data']['people'];

        $this->assertSame(['second', 'first'], array_column($people, 'name'));
    }

    public function test_cms_saves_a_job_opening(): void
    {
        $admin = $this->admin();
        $job = JobOpening::create(['title' => 'Lecturer', 'slug' => 'lecturer', 'category' => array_key_first(JobOpening::Categories), 'status' => 'draft']);

        $this->actingAs($admin)->put("/admin/content/jobs/{$job->id}", [
            'title' => 'Senior Lecturer', 'slug' => 'lecturer', 'category' => $job->category,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('Senior Lecturer', $job->fresh()->title);
    }

    public function test_addresses_from_the_previous_website_redirect(): void
    {
        $this->get('/blog')->assertRedirect('/blogs');
        $this->get('/blog/some-article')->assertRedirect('/blogs/some-article');
        $this->get('/news-events')->assertRedirect('/media');
        $this->get('/news-events/some-story')->assertRedirect('/news/some-story');
        $this->get('/press-release')->assertRedirect('/media?tab=press');
    }

    private function press(string $title, array $attributes): Post
    {
        return Post::create($attributes + [
            'type' => 'press',
            'title' => $title,
            'slug' => str($title)->slug()->value(),
            'status' => 'published',
            'published_at' => now()->subDays($title === 'With link' ? 1 : 2),
        ]);
    }
}
