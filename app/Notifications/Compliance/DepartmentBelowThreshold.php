<?php

namespace App\Notifications\Compliance;

use App\Infrastructure\Persistence\Eloquent\Models\Department;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepartmentBelowThreshold extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Department $department,
        public int $percent,
        public int $threshold = 70
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail'];
        if (config('services.slack.webhook_url') || method_exists($notifiable, 'routeNotificationForSlack')) {
            $channels[] = 'slack';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Compliance alert: {$this->department->name} at {$this->percent}%")
            ->line("Department {$this->department->name} dropped below {$this->threshold}%.")
            ->action('Open Dashboard', route('compliance.dashboard'));
    }

    public function toSlack(object $notifiable): \Illuminate\Notifications\Messages\SlackMessage
    {
        return (new \Illuminate\Notifications\Messages\SlackMessage)
            ->warning()
            ->content("⚠️ Compliance Alert: Department *{$this->department->name}* has dropped below {$this->threshold}% threshold (Current: *{$this->percent}%*).")
            ->attachment(fn ($attachment) => $attachment->title('Open Compliance Dashboard', route('compliance.dashboard')));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
