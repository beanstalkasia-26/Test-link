<?php

namespace App\Jobs;

use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use App\Models\Setting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class SendEnquiryNotification implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public Enquiry $enquiry)
    {
        $this->onQueue('mail');
    }

    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(): void
    {
        $recipients = config('namtech.enquiry_recipients') ?: array_filter(array_map('trim', explode(',', (string) Setting::get('enquiry_recipients', ''))));

        if ($recipients === []) {
            $this->enquiry->forceFill(['notification_error' => 'No enquiry recipients are configured.'])->save();
            Log::warning('Enquiry notification skipped: no recipients configured', ['enquiry_id' => $this->enquiry->id]);

            return;
        }

        Mail::to($recipients)->send(new EnquiryReceived($this->enquiry));

        $this->enquiry->forceFill(['notified_at' => now(), 'notification_error' => null])->save();
    }

    public function failed(Throwable $exception): void
    {
        $this->enquiry->forceFill(['notification_error' => Str::limit($exception->getMessage(), 1000)])->save();
        Log::error('Enquiry notification failed', ['enquiry_id' => $this->enquiry->id, 'error' => $exception->getMessage()]);
    }
}
