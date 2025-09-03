<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonthlyPOStatus extends Notification
{
    use Queueable;

    protected $poCount;

    /**
     * Create a new notification instance.
     */
    public function __construct($poCount)
    {
        $this->poCount = $poCount;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ["mail", "database"];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject("Monthly PO Status Update")
            ->line("This is your monthly update for Purchase Orders.")
            ->line("You have {$this->poCount} POs with status APPROVED this month.")
            ->action("View POs", route("po.index"));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            "message" => "MonthlyPOStatus already sent!",
        ];
    }
}
