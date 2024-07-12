<?php

namespace App\Notifications;

use App\Models\HeaderFormOvertime;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FormOvertimeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $report;
    private $details;

    /**
     * Create a new notification instance.
     */
    public function __construct(HeaderFormOvertime $report, $details)
    {
        $this->report = $report;
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            // ->from(env('MAIL_FROM_ADDRESS', 'pt.daijoindustrial@daijo.co.id'))
            ->greeting($this->details['greeting'])
            ->line(new \Illuminate\Support\HtmlString($this->details['body']))
            ->action($this->details['actionText'], $this->details['actionURL']);
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
