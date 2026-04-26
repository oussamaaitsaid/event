<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizerRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public string $userEmail,
        public string $userMessage, // ✅ renamed from $message to $userMessage
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Organizer Request from ' . $this->userName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.organizer-request',
        );
    }
}
