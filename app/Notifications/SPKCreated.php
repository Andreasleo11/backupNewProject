<?php

namespace App\Notifications;

use App\Models\SuratPerintahKerjaKomputer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SPKCreated extends Notification implements ShouldQueue
{
    use Queueable;

    private $spk;
    private $details;

    /**
     * Create a new notification instance.
     */
    public function __construct(SuratPerintahKerjaKomputer $spk, $details)
    {
        $this->spk = $spk;
        $this->details = $details;
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
            ->cc($this->details['cc'])
            ->greeting($this->details['greeting'])
            ->line('There\'s a new Surat Perintah Kerja Komputer just been created!')
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