<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobApplicationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public JobApplication $application) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->application->email, $this->application->first_name.' '.$this->application->last_name)],
            subject: 'New application: '.($this->application->job?->title ?? 'Open application'),
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.job-application-received', with: [
            'application' => $this->application->loadMissing('job'),
            'adminUrl' => route('admin.applications.show', $this->application),
        ]);
    }
}
