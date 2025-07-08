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
        $totalEmployees = count($this->employees);

        $mail = (new MailMessage)
            ->subject('📋 Missing Daily Reports Summary')
            ->greeting("Hello {$notifiable->name},")
            ->line("We’ve reviewed the daily reports submitted by your team for this month (up to today), and found that **{$totalEmployees} employee(s)** have missing entries.")
            ->line("Below is the summary of the missing daily reports per employee:");

        foreach ($this->employees as $entry) {
            $employee = $entry['employee'];
            $dates = $entry['dates'];
            $formattedDates = implode(', ', $dates);

            $mail->line('')
                ->line("🔹 **{$employee->Nama}** ({$employee->NIK})")
                ->line("   📅 Missing Dates: _{$formattedDates}_");
        }

        $mail->line('')
            ->line("Please take a moment to follow up with the concerned employees and ensure reports are completed in a timely manner.")
            ->salutation("Kind regards,\nDaily Report System");

        return $mail;
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
            'employees' => collect($this->employees)->map(fn($entry) => [
                'NIK' => $entry['employee']->NIK,
                'name' => $entry['employee']->Nama,
                'missing_dates' => $entry['dates'],
            ]),
        ];
    }
}
