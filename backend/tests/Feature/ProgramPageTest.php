<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\Media;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\PageAssert as Assert;
use Tests\TestCase;

class ProgramPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_program_modules_are_edited_in_the_cms_and_hidden_items_stay_off_the_page(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/content/programs', [
            'title' => 'Master in Robotics', 'slug' => 'master-in-robotics', 'level' => 'postgraduate',
            'why_choose' => ['heading' => null, 'items' => [
                ['icon' => 'robot', 'title' => 'Cutting Edge Labs', 'text' => 'Industrial robot cells.'],
                ['title' => 'Draft reason', '_hidden' => true],
            ]],
            'fees' => ['total' => 'Rs. 4,50,000', 'rows' => [['label' => 'Tuition Fee', 'value' => 'Rs. 2,50,000']]],
        ])->assertRedirect()->assertSessionHasNoErrors();

        $program = Program::query()->where('slug', 'master-in-robotics')->firstOrFail();
        $this->assertSame('Cutting Edge Labs', $program->details['why_choose']['items'][0]['title']);
        $this->assertTrue($program->details['why_choose']['items'][1]['_hidden']);
        $this->assertSame('Rs. 4,50,000', $program->details['fees']['total']);

        $this->actingAs($admin)->get("/admin/content/programs/{$program->id}/edit")->assertOk()
            ->assertPage(fn (Assert $page) => $page->where('values.fees.total', 'Rs. 4,50,000'));

        $this->publish($program);
        auth()->logout();
        $this->get('/programs/master-in-robotics')->assertOk()->assertPage(fn (Assert $page) => $page
            ->component('site/programs/Show')
            ->has('details.why_choose.items', 1)
            ->where('details.why_choose.items.0.title', 'Cutting Edge Labs')
            ->where('details.fees.rows.0.value', 'Rs. 2,50,000')
            ->where('tabs.fees', 'Fees & Scholarship'));
    }

    public function test_every_program_module_can_be_switched_off(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/content/programs', [
            'title' => 'B.Tech Robotics', 'slug' => 'btech-robotics', 'level' => 'undergraduate',
            'hidden_blocks' => ['fees', 'life_alumni', 'enquire'],
        ])->assertSessionHasNoErrors();

        $this->publish(Program::query()->where('slug', 'btech-robotics')->firstOrFail());
        auth()->logout();
        $this->get('/programs/btech-robotics')->assertPage(fn (Assert $page) => $page->where('hidden', ['fees', 'life_alumni', 'enquire']));
    }

    public function test_the_brochure_form_saves_a_lead_then_offers_the_download(): void
    {
        Queue::fake();
        $brochure = Media::create([
            'uuid' => (string) Str::uuid(), 'kind' => 'document', 'disk' => 'public', 'directory' => 'documents', 'filename' => 'robotics.pdf',
            'original_name' => 'robotics.pdf', 'mime_type' => 'application/pdf', 'size' => 1024, 'status' => 'ready',
        ]);
        $program = $this->publish(Program::create(['title' => 'Master in Robotics', 'slug' => 'master-in-robotics', 'level' => 'postgraduate', 'details' => ['brochure' => ['file' => $brochure->id]]]));

        $form = ['type' => 'brochure', 'program_id' => $program->id, 'name' => 'Asha Patel', 'email' => 'asha@example.com', 'consent' => true, 'website' => '', 'elapsed' => 6000];

        $this->post('/enquiries', $form)->assertSessionHasErrors(['grade', 'city']);

        $this->from('/programs/master-in-robotics')->post('/enquiries', $form + ['grade' => 'Graduate', 'city' => 'Ahmedabad'])
            ->assertRedirect('/programs/master-in-robotics')
            ->assertSessionHas('form_success.download', $brochure->documentUrl());

        $enquiry = Enquiry::query()->firstOrFail();
        $this->assertSame('brochure', $enquiry->type);
        $this->assertStringContainsString('Ahmedabad', $enquiry->message);
    }

    public function test_a_brochure_request_without_an_uploaded_file_is_saved_and_promised_by_email(): void
    {
        Queue::fake();
        $program = $this->publish(Program::create(['title' => 'iPTP Automation', 'slug' => 'iptp-automation', 'level' => 'technical']));

        $this->post('/enquiries', [
            'type' => 'brochure', 'program_id' => $program->id, 'name' => 'Asha Patel', 'email' => 'asha@example.com',
            'grade' => 'Diploma', 'city' => 'Surat', 'consent' => true, 'website' => '', 'elapsed' => 6000,
        ])->assertSessionHas('form_success.message', 'Thank you, Asha Patel. Our team will email you the brochure shortly.');

        $this->assertSame('brochure', Enquiry::query()->firstOrFail()->type);
    }

    public function test_the_programs_page_lists_programmes_by_level(): void
    {
        $this->publish(Program::create(['title' => 'B.Tech Robotics', 'slug' => 'btech-robotics', 'level' => 'undergraduate']));
        $this->publish(Program::create(['title' => 'Master in Robotics', 'slug' => 'master-in-robotics', 'level' => 'postgraduate']));
        $page = $this->page('Programs', 'programs');
        $page->sections()->create(['type' => 'program_levels', 'is_visible' => true, 'sort_order' => 1, 'data' => ['items' => [
            ['level' => 'undergraduate', 'heading' => 'Bachelors Programmes'],
            ['level' => 'postgraduate', 'heading' => 'Masters Programmes'],
        ]]]);

        $this->get('/programs')->assertOk()->assertPage(fn (Assert $p) => $p
            ->component('site/programs/Index')
            ->where('sections.0.data.items.0.programs.0.title', 'B.Tech Robotics')
            ->where('sections.0.data.items.1.programs.0.url', '/programs/master-in-robotics'));
    }

    public function test_iptp_programs_are_served_under_the_school_of_technical_education(): void
    {
        $program = $this->publish(Program::create(['title' => 'iPTP Automation', 'slug' => 'iptp-automation', 'level' => 'technical']));

        $this->assertSame('/school-of-technical-education/iptp/automation', $program->publicPath());

        $this->get('/school-of-technical-education/iptp/automation')->assertOk()->assertPage(fn (Assert $p) => $p
            ->component('site/programs/Show')
            ->where('program.url', '/school-of-technical-education/iptp/automation')
            ->where('breadcrumbs.1.url', '/school-of-technical-education/iptp'));

        $this->get('/programs/iptp-automation')->assertRedirect('/school-of-technical-education/iptp/automation')->assertStatus(301);
        $this->get('/school-of-technical-education/iptp/unknown')->assertNotFound();
    }

    public function test_the_iptp_page_shows_the_draft_iptp_record_in_the_program_layout_with_its_program_list(): void
    {
        $school = $this->publish(Page::create(['title' => 'School of Technical Education', 'slug' => 'school-of-technical-education']));
        $page = $this->publish(Page::create(['title' => 'iPTP', 'slug' => 'iptp', 'parent_id' => $school->id]));
        $page->sections()->create(['type' => 'page_hero', 'is_visible' => true, 'sort_order' => 0, 'data' => ['title' => 'iPTP']]);
        $page->sections()->create(['type' => 'programs_list', 'is_visible' => true, 'sort_order' => 1, 'data' => ['heading' => 'Explore iPTP Programs']]);

        $this->get('/school-of-technical-education/iptp')->assertOk()->assertPage(fn (Assert $p) => $p->component('site/Page'));

        Program::create(['title' => 'International Professional Technologist Program (iPTP)', 'slug' => Program::IptpOverviewSlug, 'level' => 'technical']);

        $this->get('/school-of-technical-education/iptp')->assertOk()->assertPage(fn (Assert $p) => $p
            ->component('site/programs/Show')
            ->where('program.url', '/school-of-technical-education/iptp')
            ->has('sections', 1)
            ->where('sections.0.type', 'programs_list'));

        $this->get('/programs')->assertOk()->assertPage(fn (Assert $p) => $p->has('programs', 0));
    }

    public function test_empty_program_modules_show_labelled_samples_instead_of_disappearing(): void
    {
        $this->publish(Program::create(['title' => 'Empty Program', 'slug' => 'empty-program', 'level' => 'postgraduate']));

        $this->get('/programs/empty-program')->assertOk()->assertPage(fn (Assert $p) => $p
            ->component('site/programs/Show')
            ->has('details.faqs.items', 3)
            ->where('details.faqs.items.0.question', fn ($q) => str_starts_with($q, 'SAMPLE'))
            ->where('details.innovations.items.0.date', 'January 10, 2026')
            ->where('details.innovations.items.0.text', fn ($text) => str_starts_with($text, 'Sample story.'))
            ->has('details.awards.items', 4)
            ->where('details.awards.items.0.date', 'January 10, 2026')
            ->where('details.awards.items.0.text', fn ($text) => str_starts_with($text, 'Sample award.'))
            ->has('details.structure.semesters', 3)
            ->has('details.loans.providers.0.items', 2)
            ->has('faculty', 4));
    }

    public function test_a_section_without_matching_content_shows_labelled_samples(): void
    {
        $page = $this->publish(Page::create(['title' => 'Events Page', 'slug' => 'events-page']));
        $page->sections()->create(['type' => 'events_carousel', 'is_visible' => true, 'sort_order' => 0, 'data' => ['heading' => 'Upcoming Events']]);
        $page->sections()->create(['type' => 'news_carousel', 'is_visible' => true, 'sort_order' => 1, 'data' => ['heading' => 'News']]);

        $this->get('/events-page')->assertOk()->assertPage(fn (Assert $p) => $p
            ->has('sections.0.data.events', 3)
            ->where('sections.0.data.events.0.title', 'SAMPLE – Event 1')
            ->has('sections.1.data.posts', 4));
    }

    public function test_recruiter_quotes_replace_template_placeholders_and_preserve_cms_content(): void
    {
        $placeholder = ['name' => 'SAMPLE – Company name', 'quote' => 'SAMPLE – replace with a real recruiter quote.'];
        $logo = Media::create([
            'uuid' => (string) Str::uuid(), 'kind' => 'image', 'disk' => 'public', 'directory' => 'media/cisco', 'filename' => 'cisco.webp',
            'original_name' => 'cisco.jpg', 'mime_type' => 'image/jpeg', 'size' => 1024, 'status' => 'ready',
            'width' => 336, 'height' => 136, 'variants' => ['widths' => [336], 'formats' => ['webp']],
        ]);
        $this->publish(Partner::create(['name' => 'Cisco', 'slug' => 'cisco', 'logo_id' => $logo->id]));
        $program = $this->publish(Program::create([
            'title' => 'Sample Program', 'slug' => 'sample-program', 'level' => 'postgraduate',
            'details' => [
                'recruiter_quotes' => ['items' => [$placeholder, $placeholder]],
                'outcomes' => ['logos' => [['name' => 'TUM Asia', 'image' => $logo->id]]],
            ],
        ]));

        $this->get('/programs/sample-program')->assertOk()->assertPage(fn (Assert $p) => $p
            ->has('details.recruiter_quotes.items', 8)
            ->where('details.recruiter_quotes.items.0.name', 'Cisco')
            ->where('details.recruiter_quotes.items.0.logo.id', $logo->id)
            ->where('details.recruiter_quotes.items.0.quote', fn ($quote) => str_starts_with($quote, 'Sample quote.'))
            ->where('details.recruiter_quotes.items.1.name', 'Sample recruiter 2')
            ->where('details.recruiter_quotes.items.3.name', 'TUM Asia')
            ->where('details.recruiter_quotes.items.3.logo.id', $logo->id));

        $program->update(['details' => ['recruiter_quotes' => ['items' => [
            $placeholder,
            ['name' => 'Engineering team', 'quote' => 'Our graduates applied their skills from day one.', 'logo' => $logo->id],
        ]]]]);

        $this->get('/programs/sample-program')->assertOk()->assertPage(fn (Assert $p) => $p
            ->has('details.recruiter_quotes.items', 2)
            ->where('details.recruiter_quotes.items.1.name', 'Engineering team')
            ->where('details.recruiter_quotes.items.1.logo.id', $logo->id)
            ->where('details.recruiter_quotes.items.1.quote', 'Our graduates applied their skills from day one.'));
    }
}
