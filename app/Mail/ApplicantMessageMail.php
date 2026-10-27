<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicantMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public JobApplication $application,
        public string $subjectLine,
        public string $body,
        public Address $replyToAdmin,
    ) {
    }

    // The site sends from a no-reply address, so replies go to the admin who wrote.
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
            replyTo: [$this->replyToAdmin],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.applicant-message',
        );
    }
}
