<?php

namespace App\Jobs;

use App\Mail\JobApplicationReceived;
use App\Models\JobApplication;
use App\Models\Setting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class SendJobApplicationNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public JobApplication $application)
    {
        $this->onQueue('mail');
    }

    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(): void
    {
        $list = (string) (Setting::get('careers_recipients') ?: Setting::get('enquiry_recipients', ''));
        $recipients = config('namtech.enquiry_recipients') ?: array_filter(array_map('trim', explode(',', $list)));
        if ($recipients === []) {
            $this->application->forceFill(['notification_error' => 'No recipients are configured.'])->save();

            return;
        }

        Mail::to($recipients)->send(new JobApplicationReceived($this->application));
        $this->application->forceFill(['notified_at' => now(), 'notification_error' => null])->save();
    }

    public function failed(Throwable $exception): void
    {
        $this->application->forceFill(['notification_error' => Str::limit($exception->getMessage(), 1000)])->save();
        Log::error('Job application notification failed', ['application_id' => $this->application->id, 'error' => $exception->getMessage()]);
    }
}
