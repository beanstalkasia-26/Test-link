<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Enums\Role;
use App\Models\ContentRevision;
use App\Models\Page;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\PageAssert as Assert;
use Tests\TestCase;

class ContentWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function sections(string $badge = 'Industry 4.0'): array
    {
        return [
            ['type' => 'card_grid', 'is_visible' => true, 'data' => ['heading' => 'Cards', 'cards' => [['title' => 'Make in India'], ['title' => $badge]]]],
            ['type' => 'rich_text', 'is_visible' => true, 'data' => ['heading' => 'Second', 'body' => '<p>Two</p>']],
            ['type' => 'cta_band', 'is_visible' => true, 'data' => ['heading' => 'Third']],
        ];
    }

    public function test_guests_are_redirected_to_login_and_there_is_no_registration(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk()->assertJsonPath('component', 'admin/auth/Login')->assertJsonPath('props.auth', null);
        $this->get('/admin/register')->assertNotFound();
        $this->get('/register')->assertNotFound();
    }

    public function test_users_without_an_admin_role_or_inactive_users_cannot_use_the_cms(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin')->assertRedirect('/admin/login');

        $inactive = $this->admin(Role::Editor);
        $inactive->update(['is_active' => false]);
        $this->post('/admin/login', ['email' => $inactive->email, 'password' => 'password'])->assertSessionHasErrors('email');
    }

    public function test_login_is_throttled(): void
    {
        $user = $this->admin(Role::Editor);
        foreach (range(1, 5) as $attempt) {
            $this->post('/admin/login', ['email' => $user->email, 'password' => 'wrong-password']);
        }
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password'])->assertStatus(429);
        $this->assertDatabaseHas('audit_logs', ['action' => 'auth.failed']);
    }

    public function test_privileged_roles_must_enrol_in_two_factor_when_enforced(): void
    {
        config(['namtech.enforce_two_factor' => true]);
        $publisher = $this->admin(Role::Publisher);
        $editor = $this->admin(Role::Editor);

        $this->actingAs($publisher)->get('/admin')->assertRedirect('/admin/account');
        $this->actingAs($publisher)->get('/admin/account')->assertOk();
        $this->actingAs($editor)->get('/admin')->assertOk();
    }

    public function test_editors_create_drafts_and_cannot_publish(): void
    {
        $editor = $this->admin(Role::Editor);

        $this->actingAs($editor)->post('/admin/content/pages', ['title' => 'Life @ NAMTECH', 'slug' => 'life', 'template' => 'default', 'sections' => $this->sections()])
            ->assertRedirect();
        $page = Page::query()->where('slug', 'life')->firstOrFail();
        $this->assertSame(ContentStatus::Draft, $page->status);
        $this->assertCount(3, $page->sections);

        $this->actingAs($editor)->post("/admin/content/pages/{$page->id}/publish")->assertForbidden();
        $this->actingAs($editor)->post("/admin/content/pages/{$page->id}/submit")->assertRedirect();
        $this->assertSame('pending', $page->fresh()->review_state);
        $this->actingAs($editor)->delete("/admin/content/pages/{$page->id}")->assertForbidden();
        $this->actingAs($editor)->get('/admin/settings')->assertForbidden();
        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
        $this->actingAs($editor)->get('/admin/enquiries')->assertForbidden();
    }

    public function test_edits_to_live_content_are_held_until_a_publisher_publishes_them(): void
    {
        $editor = $this->admin(Role::Editor);
        $publisher = $this->admin(Role::Publisher);
        $page = $this->page('Home', 'home', [], template: 'home');

        $this->actingAs($editor)->put("/admin/content/pages/{$page->id}", ['title' => 'Home', 'slug' => 'home', 'template' => 'home', 'sections' => $this->sections('Industry 5.0')])
            ->assertSessionHas('success');

        $this->assertCount(0, $page->fresh()->sections);
        $working = $page->workingCopy()->firstOrFail();
        $this->assertSame(['card_grid', 'rich_text', 'cta_band'], array_column($working->data['relations']['sections'], 'type'));
        $this->get('/')->assertPage(fn (Assert $p) => $p->has('sections', 0));

        $this->actingAs($editor)->get("/admin/preview/pages/{$page->id}")->assertOk()
            ->assertPage(fn (Assert $p) => $p->where('preview', true)->where('sections.0.data.cards.1.title', 'Industry 5.0'));

        $this->actingAs($publisher)->post("/admin/content/pages/{$page->id}/publish")->assertSessionHas('success');
        $this->assertNull($page->fresh()->workingCopy);
        $this->assertSame(['card_grid', 'rich_text', 'cta_band'], $page->fresh()->sections->pluck('type')->all());

        auth()->logout();
        $this->get('/')->assertPage(fn (Assert $p) => $p->where('sections.0.data.cards.1.title', 'Industry 5.0'));
    }

    public function test_section_data_is_validated_and_rich_text_is_sanitised(): void
    {
        $admin = $this->admin();
        $sections = [['type' => 'rich_text', 'is_visible' => true, 'data' => ['heading' => 'X', 'body' => '<p onclick="alert(1)">Hi<script>alert(1)</script> <a href="javascript:alert(1)">bad</a> <a href="/ok">ok</a></p><iframe src="https://evil.example/x"></iframe>']]];

        $this->actingAs($admin)->post('/admin/content/pages', ['title' => 'T', 'template' => 'default', 'sections' => $sections])->assertRedirect();
        $body = Page::query()->firstOrFail()->sections->first()->data['body'];
        $this->assertStringNotContainsString('script', $body);
        $this->assertStringNotContainsString('onclick', $body);
        $this->assertStringNotContainsString('javascript:', $body);
        $this->assertStringNotContainsString('evil.example', $body);
        $this->assertStringContainsString('href="/ok"', $body);

        $this->actingAs($admin)->post('/admin/content/pages', ['title' => 'Bad', 'template' => 'default', 'sections' => [
            ['type' => 'not_a_component', 'data' => []],
            ['type' => 'cta_band', 'data' => ['heading' => '', 'cta_url' => 'javascript:alert(1)']],
        ]])->assertSessionHasErrors(['sections.0.type', 'sections.1.data.heading', 'sections.1.data.cta_url']);
    }

    public function test_hidden_sections_can_be_saved_incomplete_but_are_still_format_checked(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/content/pages', ['title' => 'Draft', 'template' => 'default', 'sections' => [
            ['type' => 'testimonials', 'is_visible' => false, 'data' => ['heading' => 'Student Testimonials', 'items' => []]],
        ]])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertFalse(Page::query()->where('title', 'Draft')->firstOrFail()->sections->first()->is_visible);

        $this->actingAs($admin)->post('/admin/content/pages', ['title' => 'Bad', 'template' => 'default', 'sections' => [
            ['type' => 'cta_band', 'is_visible' => false, 'data' => ['heading' => 'X', 'cta_url' => 'javascript:alert(1)']],
            ['type' => 'testimonials', 'is_visible' => true, 'data' => ['heading' => 'Shown', 'items' => []]],
        ]])->assertSessionHasErrors(['sections.0.data.cta_url', 'sections.1.data.items']);
    }

    public function test_scheduled_content_is_published_by_the_scheduler(): void
    {
        $publisher = $this->admin(Role::Publisher);
        $program = Program::create(['title' => 'Future program', 'slug' => 'future', 'level' => 'postgraduate']);

        $this->actingAs($publisher)->post("/admin/content/programs/{$program->id}/schedule", ['scheduled_for' => now()->subMinute()->toDateTimeString()])
            ->assertSessionHasErrors('scheduled_for');
        $this->actingAs($publisher)->post("/admin/content/programs/{$program->id}/schedule", ['scheduled_for' => now()->addHour()->toDateTimeString()])
            ->assertSessionHas('success');
        $this->assertSame(ContentStatus::Scheduled, $program->fresh()->status);

        $this->travel(2)->hours();
        $this->artisan('content:publish-scheduled')->assertSuccessful();
        $this->assertSame(ContentStatus::Published, $program->fresh()->status);
        $this->assertNull($program->fresh()->scheduled_for);
    }

    public function test_revisions_can_be_restored(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post('/admin/content/programs', ['title' => 'Original', 'slug' => 'p', 'level' => 'undergraduate']);
        $program = Program::query()->firstOrFail();
        $this->actingAs($admin)->put("/admin/content/programs/{$program->id}", ['title' => 'Changed', 'slug' => 'p', 'level' => 'undergraduate']);
        $this->assertSame('Changed', $program->fresh()->title);

        $first = ContentRevision::query()->where('note', 'Created')->firstOrFail();
        $this->actingAs($admin)->post("/admin/content/programs/{$program->id}/revisions/{$first->id}/restore")->assertSessionHas('success');
        $this->assertSame('Original', $program->fresh()->title);
    }

    public function test_slugs_must_be_unique_and_well_formed(): void
    {
        $admin = $this->admin();
        Program::create(['title' => 'A', 'slug' => 'taken', 'level' => 'undergraduate']);

        $this->actingAs($admin)->post('/admin/content/programs', ['title' => 'B', 'slug' => 'taken', 'level' => 'undergraduate'])->assertSessionHasErrors('slug');
        $this->actingAs($admin)->post('/admin/content/programs', ['title' => 'B', 'slug' => 'Bad Slug!', 'level' => 'undergraduate'])->assertSessionHasErrors('slug');
    }

    public function test_dashboard_shows_real_counts(): void
    {
        $admin = $this->admin();
        $this->page('About', 'about');
        Page::create(['title' => 'Draft', 'slug' => 'draft', 'template' => 'default'])->forceFill(['review_state' => 'pending'])->save();

        $this->actingAs($admin)->get('/admin')->assertPage(fn (Assert $p) => $p
            ->component('admin/Dashboard')
            ->where('counts.published_pages', 1)
            ->where('counts.awaiting_review', 1)
            ->has('awaitingReview', 1));
    }
}
