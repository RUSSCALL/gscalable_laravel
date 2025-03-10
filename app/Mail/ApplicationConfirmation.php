<?php

namespace App\Mail;

use App\Models\JobPosting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use App\Models\JobApplication;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApplicationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $application;
    public $jobPosting;

    /**
     * Create a new message instance.
     */
    public function __construct(JobApplication $application, JobPosting $jobPosting)
    {
        $this->application = $application;
        $this->jobPosting = $jobPosting;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Application Confirmation - ' . $this->jobPosting->title,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.application-confirmation',
        );
    }
}