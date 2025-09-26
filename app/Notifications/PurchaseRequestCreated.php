<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseRequestCreated extends Notification implements ShouldQueue
{
    use Queueable;

    private $pr;

    private $details;

    /**
     * Create a new notification instance.
     */
    public function __construct(PurchaseRequest $pr, $details)
    {
        $this->pr = $pr;
        $this->details = $details;
    }

    /**
     * Get the notification's delivery channels.
     *`1`1
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('There\'s a new Purchase Request has just been created!')
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
            'message' => 'PR with id = '.$this->pr->id.' has just been created!',
            'status' => $this->pr->status,
        ];
    }
}
