<?php

namespace App\Mail;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterviewScheduled extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Interview $interview, public ?Address $replyToAdmin = null)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Interview scheduled - ' . $this->interview->application->jobPosting->title,
            replyTo: array_filter([$this->replyToAdmin]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.interview-scheduled',
        );
    }
}
