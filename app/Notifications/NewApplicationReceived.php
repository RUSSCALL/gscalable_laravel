<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewApplicationReceived extends Notification
{
    use Queueable;

    public function __construct(public JobApplication $application)
    {
    }

    public function via(object $notifiable): array
    {
        return $notifiable->notificationChannels('new_application');
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New application: ' . $this->application->jobPosting->title)
            ->line($this->message())
            ->action('View application', route('admin.applicants.show', $this->application));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New application received',
            'message' => $this->message(),
            'url' => route('admin.applicants.show', $this->application),
            'icon' => 'bi-person-plus',
        ];
    }

    private function message(): string
    {
        return $this->application->full_name . ' applied for ' . $this->application->jobPosting->title . '.';
    }
}
