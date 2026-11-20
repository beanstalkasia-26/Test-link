<?php

namespace App\Mail;

use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnquiryReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Enquiry $enquiry) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->enquiry->email, $this->enquiry->name)],
            subject: 'New '.(Enquiry::Types[$this->enquiry->type] ?? 'website').' enquiry #'.$this->enquiry->id,
        );
    }

    public function content(): Content
    {
        return new Content(markdown: 'mail.enquiry-received', with: [
            'enquiry' => $this->enquiry->loadMissing('program'),
            'adminUrl' => route('admin.enquiries.show', $this->enquiry),
        ]);
    }
}
