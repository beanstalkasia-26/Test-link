<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Jobs\SendEnquiryNotification;
use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use App\Models\NewsletterSubscriber;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FormsTest extends TestCase
{
    use RefreshDatabase;

    private function enquiry(array $overrides = []): array
    {
        return array_merge([
            'type' => 'contact',
            'name' => 'Asha Patel',
            'email' => 'asha@example.com',
            'phone' => '+91 98765 43210',
            'subject' => 'Visit',
            'message' => 'I would like to visit the campus next month.',
            'consent' => true,
            'website' => '',
            'elapsed' => 6000,
        ], $overrides);
    }

    public function test_enquiries_are_saved_before_notifications_are_queued(): void
    {
        Queue::fake();

        $this->from('/contact')->post('/enquiries', $this->enquiry())->assertRedirect('/contact')->assertSessionHas('form_success');

        $enquiry = Enquiry::query()->firstOrFail();
        $this->assertSame('new', $enquiry->status);
        $this->assertTrue($enquiry->consent);
        Queue::assertPushed(SendEnquiryNotification::class, fn ($job) => $job->enquiry->is($enquiry));
    }

    public function test_notification_is_emailed_and_recorded(): void
    {
        Mail::fake();
        $this->post('/enquiries', $this->enquiry());

        Mail::assertSent(EnquiryReceived::class, fn ($mail) => $mail->hasTo('enquiries@example.test') && $mail->hasReplyTo('asha@example.com'));
        $this->assertNotNull(Enquiry::query()->first()->notified_at);
    }

    public function test_partnership_enquiry_keeps_the_selected_collaboration_type(): void
    {
        Queue::fake();

        $path = '/partnerships/corporate/sponsored-research';
        $this->from($path)->post('/enquiries', $this->enquiry([
            'type' => 'partnership',
            'subject' => 'Sponsored Research',
            'organisation' => 'Sample engineering team',
            'message' => 'We would like to discuss a research collaboration.',
        ]))->assertRedirect($path)->assertSessionHas('form_success.form', 'enquiry');

        $this->assertDatabaseHas('enquiries', ['type' => 'partnership', 'subject' => 'Sponsored Research']);
    }

    public function test_failed_notifications_keep_the_enquiry_and_record_the_error(): void
    {
        $this->post('/enquiries', $this->enquiry());
        $enquiry = Enquiry::query()->firstOrFail();

        (new SendEnquiryNotification($enquiry))->failed(new \RuntimeException('SMTP unavailable'));

        $this->assertSame('SMTP unavailable', $enquiry->fresh()->notification_error);
        $this->assertDatabaseCount('enquiries', 1);
    }

    public function test_validation_requires_consent_and_a_program_for_program_enquiries(): void
    {
        $this->post('/enquiries', $this->enquiry(['consent' => false, 'email' => 'not-an-email', 'message' => 'short']))
            ->assertSessionHasErrors(['consent', 'email', 'message']);

        $this->post('/enquiries', $this->enquiry(['type' => 'program']))->assertSessionHasErrors('program_id');

        $draft = Program::create(['title' => 'Draft', 'slug' => 'draft', 'level' => 'undergraduate']);
        $this->post('/enquiries', $this->enquiry(['type' => 'program', 'program_id' => $draft->id]))->assertSessionHasErrors('program_id');

        $live = $this->publish(Program::create(['title' => 'Live', 'slug' => 'live', 'level' => 'undergraduate']));
        $this->post('/enquiries', $this->enquiry(['type' => 'program', 'program_id' => $live->id]))->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('form_success');
        $this->assertSame($live->id, Enquiry::query()->first()->program_id);
    }

    public function test_spam_submissions_are_rejected(): void
    {
        $this->post('/enquiries', $this->enquiry(['website' => 'http://spam.example']))->assertSessionHasErrors('message');
        $this->post('/enquiries', $this->enquiry(['elapsed' => 300]))->assertSessionHasErrors('message');
        $this->assertDatabaseCount('enquiries', 0);
    }

    public function test_form_submissions_are_throttled(): void
    {
        Queue::fake();
        foreach (range(1, 5) as $i) {
            $this->post('/enquiries', $this->enquiry());
        }
        $this->post('/enquiries', $this->enquiry())->assertStatus(429);
    }

    public function test_newsletter_signup_is_idempotent_and_does_not_reveal_existing_subscribers(): void
    {
        $this->post('/newsletter', ['newsletter_email' => 'Reader@Example.com', 'newsletter_consent' => true])->assertSessionHas('form_success');
        $this->post('/newsletter', ['newsletter_email' => 'reader@example.com', 'newsletter_consent' => true])->assertSessionHas('form_success');

        $this->assertDatabaseCount('newsletter_subscribers', 1);
        $subscriber = NewsletterSubscriber::query()->first();
        $this->assertSame('reader@example.com', $subscriber->email);

        $this->post('/newsletter', ['newsletter_email' => 'x@example.com'])->assertSessionHasErrors('newsletter_consent');

        $this->get('/newsletter/unsubscribe/'.$subscriber->unsubscribe_token)->assertRedirect('/');
        $this->assertSame('unsubscribed', $subscriber->fresh()->status);
    }

    public function test_csv_export_requires_permission_and_neutralises_formulas(): void
    {
        Queue::fake();
        $this->post('/enquiries', $this->enquiry(['name' => '=HYPERLINK("http://evil")']));

        $this->actingAs($this->admin(Role::Publisher))->get('/admin/enquiries')->assertOk();
        $this->actingAs($this->admin(Role::Publisher))->get('/admin/enquiries/export')->assertForbidden();
        $this->actingAs($this->admin(Role::Editor))->get('/admin/enquiries')->assertForbidden();

        $response = $this->actingAs($this->admin(Role::SuperAdmin))->get('/admin/enquiries/export');
        $response->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertDatabaseHas('audit_logs', ['action' => 'enquiry.exported']);
    }
}
