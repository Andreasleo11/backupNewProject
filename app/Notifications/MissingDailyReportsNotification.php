<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MissingDailyReportsNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $employees;

    /**
     * Create a new notification instance.
     */
    public function __construct($employees)
    {
        $this->employees = $employees;
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
        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject('📋 Missing Daily Reports Summary')
            ->markdown('emails.notifications.missing-daily-reports', [
                'notifiable' => $notifiable,
                'employees' => $this->employees,
            ]);
    }


    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Missing Daily Reports',
            'total_employees' => count($this->employees),
            'employees' => collect($this->employees)->map(fn($entry) => [
                'name' => $entry['employee']->Nama,
                'NIK' => $entry['employee']->NIK,
                'missing_dates' => $entry['dates'],
            ]),
        ];
    }
}
