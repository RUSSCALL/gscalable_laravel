<?php

namespace App\Mail;

use App\Models\JobAlertSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The double opt-in email. Nothing is ever sent to a subscription until the
 * link in this message is followed, so an address typed in by somebody else
 * never receives alerts.
 */
class JobAlertConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public JobAlertSubscription $subscription)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirm your job alerts',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.job-alert-confirm',
            with: [
                'confirmUrl' => $this->subscription->confirmationUrl(),
                'target' => $this->subscription->target_description,
            ],
        );
    }
}
