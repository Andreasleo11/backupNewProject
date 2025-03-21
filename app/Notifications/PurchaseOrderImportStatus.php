<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderImportStatus extends Notification implements ShouldQueue
{
    use Queueable;

    public $status;
    public $message;

    /**
     * Create a new notification instance.
     */
    public function __construct($status, $message)
    {
        $this->status = $status;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Purchase Order Import Status')
                    ->line($this->message)
                    ->action('View Import Logs', url('/admin/import-logs'))
                    ->line('Thank you for using our application.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message
        ];
    }
}
