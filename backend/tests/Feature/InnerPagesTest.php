<?php

namespace Tests\Feature;

use App\Jobs\SendJobApplicationNotification;
use App\Models\Event;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\Leader;
use App\Models\Post;
use App\Models\Program;
use App\Models\Publication;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\PageAssert as Assert;
use Tests\TestCase;

class InnerPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_with_sections_renders_as_a_landing_page(): void
    {
        $school = $this->publish(School::create(['name' => 'School of Sustainability', 'slug' => 'school-of-sustainability']));
        $school->sections()->create(['type' => 'pillars', 'data' => ['heading' => 'Core Pillars', 'items' => [['title' => 'Energy', 'text' => 'Renewables']]], 'sort_order' => 0, 'is_visible' => true]);

        $this->get('/schools/school-of-sustainability')->assertOk()->assertPage(fn (Assert $page) => $page
            ->component('site/Page')
            ->where('sections.0.type', 'pillars')
            ->where('sections.0.data.items.0.title', 'Energy'));
    }

    public function test_program_pages_use_the_program_template_with_extra_sections_below(): void
    {
        $program = $this->publish(Program::create(['title' => 'Master in Sustainability', 'slug' => 'master-in-sustainability', 'level' => 'postgraduate']));
        $program->sections()->create(['type' => 'intro', 'data' => ['heading' => 'Extra block'], 'sort_order' => 0, 'is_visible' => true]);

        $this->get('/programs/master-in-sustainability')->assertOk()->assertPage(fn (Assert $page) => $page
            ->component('site/programs/Show')
            ->where('tabs.overview', 'Program Overview')
            ->where('sections.0.data.heading', 'Extra block')
            ->where('seo.jsonLd.1.@type', 'Course'));
    }

    public function test_events_are_listed_by_category_and_have_detail_pages(): void
    {
        $this->publish(Event::create(['category' => 'conference', 'title' => 'Sustainable Manufacturing Summit', 'slug' => 'summit', 'starts_at' => now()->addMonth(), 'venue' => 'Auditorium 3']));
        $this->publish(Event::create(['category' => 'masterclass', 'title' => 'Robotics masterclass', 'slug' => 'robotics', 'starts_at' => now()->addMonth()]));
        Event::create(['category' => 'conference', 'title' => 'Draft event', 'slug' => 'draft', 'starts_at' => now()->addMonth()]);

        $this->get('/events?category=conference')->assertOk()->assertPage(fn (Assert $page) => $page
            ->component('site/events/Index')
            ->has('events.data', 1)
            ->where('events.data.0.title', 'Sustainable Manufacturing Summit'));
        $this->get('/events/summit')->assertOk()->assertPage(fn (Assert $page) => $page->component('site/events/Show')->where('seo.jsonLd.1.@type', 'Event'));
        $this->get('/events/draft')->assertNotFound();
    }

    public function test_media_hub_switches_between_news_press_and_announcements(): void
    {
        $this->publish(Post::create(['type' => 'news', 'title' => 'Campus news', 'slug' => 'campus-news', 'body' => '<p>News</p>']));
        $this->publish(Post::create(['type' => 'press', 'title' => 'Press release', 'slug' => 'press-release', 'body' => '<p>Press</p>']));

        $this->get('/media')->assertOk()->assertPage(fn (Assert $page) => $page->component('site/posts/Media')->where('tab', 'news')->where('featured.title', 'Campus news'));
        $this->get('/media?tab=press')->assertOk()->assertPage(fn (Assert $page) => $page->where('tab', 'press')->has('posts.data', 1)->where('posts.data.0.title', 'Press release'));
    }

    public function test_leaders_and_publications_have_public_pages(): void
    {
        $this->publish(Leader::create(['group' => 'advisory', 'name' => 'Dr. Ashish Nanda', 'slug' => 'dr-ashish-nanda', 'achievements' => [['year' => '2026', 'title' => 'Award']]]));
        $this->publish(Publication::create(['title' => 'Semiconductor research', 'slug' => 'semiconductor-research', 'published_on' => '2025-01-12']));

        $this->get('/about/people/dr-ashish-nanda')->assertOk()->assertPage(fn (Assert $page) => $page->component('site/Leader')->where('person.group', 'Advisory Council')->has('person.achievements', 1));
        $this->get('/research-and-consultancy/publications')->assertOk()->assertPage(fn (Assert $page) => $page->component('site/research/Index')->has('publications.data', 1));
        $this->get('/research-and-consultancy/publications/semiconductor-research')->assertOk();
    }

    public function test_job_applications_store_the_cv_privately_and_queue_a_notification(): void
    {
        Queue::fake();
        Storage::fake('local');
        $job = $this->publish(JobOpening::create(['category' => 'academic', 'title' => 'Assistant Professor', 'slug' => 'assistant-professor']));

        $this->from('/careers')->post('/careers/apply', [
            'job_opening_id' => $job->id,
            'first_name' => 'Asha',
            'last_name' => 'Patel',
            'email' => 'asha@example.com',
            'resume' => UploadedFile::fake()->create('cv.pdf', 200, 'application/pdf'),
            'consent' => true,
            'website' => '',
        ])->assertRedirect('/careers')->assertSessionHas('form_success');

        $application = JobApplication::query()->firstOrFail();
        Storage::disk('local')->assertExists($application->resume_path);
        $this->assertStringStartsWith('applications/', $application->resume_path);
        Queue::assertPushed(SendJobApplicationNotification::class);
    }

    public function test_job_applications_require_a_pdf_or_word_cv_and_an_open_position(): void
    {
        Storage::fake('local');
        $draft = JobOpening::create(['category' => 'academic', 'title' => 'Draft role', 'slug' => 'draft-role']);

        $this->post('/careers/apply', [
            'job_opening_id' => $draft->id,
            'first_name' => 'Asha',
            'last_name' => 'Patel',
            'email' => 'asha@example.com',
            'resume' => UploadedFile::fake()->create('cv.exe', 10, 'application/octet-stream'),
            'consent' => true,
        ])->assertSessionHasErrors(['job_opening_id', 'resume']);

        $this->assertSame(0, JobApplication::query()->count());
    }
}
