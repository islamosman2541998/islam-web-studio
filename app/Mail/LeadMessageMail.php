<?php

namespace App\Mail;

use App\Models\LeadMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class LeadMessageMail extends Mailable
{
    public function __construct(public LeadMessage $leadMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: (string) $this->leadMessage->subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.lead-message',
            text: 'mail.lead-message-text',
            with: ['body' => $this->leadMessage->body, 'rtl' => ($this->leadMessage->lead?->locale ?: 'ar') === 'ar'],
        );
    }
}
