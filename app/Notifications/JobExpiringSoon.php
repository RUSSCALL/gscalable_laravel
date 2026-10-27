<?php

namespace App\Notifications;

use App\Models\JobPosting;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class JobExpiringSoon extends Notification
{
    use Queueable;

    public function __construct(public JobPosting $job)
    {
    }

    public function via(object $notifiable): array
    {
        return $notifiable->notificationChannels('job_expiring');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Job closing soon: ' . $this->job->title)
            ->line($this->message())
            ->action('View job', route('jobs.show', $this->job->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Job posting expiring soon',
            'message' => $this->message(),
            'url' => route('jobs.show', $this->job->id),
            'icon' => 'bi-hourglass-split',
        ];
    }

    private function message(): string
    {
        return $this->job->title . ' stops taking applications on ' . $this->job->application_deadline->format('M j, Y') . '.';
    }
}
