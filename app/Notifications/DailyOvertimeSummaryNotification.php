<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class DailyOvertimeSummaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $reports;
    public $status;

    /**
     * Create a new notification instance.
     */
    public function __construct($reports, $status)
    {
        $this->reports = $reports;
        $this->status = $status;
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
        $name = ucwords($notifiable->name);
        $mail = (new MailMessage)
            ->subject("Overtime Approval Summary – " . ucwords(str_replace('-', ' ', $this->status)))
            ->greeting("Dear {$name},")
            ->line("Here are the overtime forms waiting for your action:");

        // Build the HTML table
        $table = '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; font-family: Arial, sans-serif; font-size: 14px;">
        <thead style="background-color: #f0f0f0;">
            <tr>
                <th>ID</th>
                <th>Department</th>
                <th>Overtime Date</th>
                <th>Created At</th>
                <th>Created By</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($this->reports as $report) {
            $url = route('formovertime.detail', $report->id);
            $table .= "<tr>
            <td>{$report->id}</td>
            <td>{$report->department->name}</td>
            <td>{$report->details[0]->start_date}</td>
            <td>{$report->created_at}</td>
            <td>{$report->user->name}</td>
            <td><a href=\"{$url}\" target=\"_blank\">View</a></td>
        </tr>";
        }

        $table .= '</tbody></table>';

        $mail->line(new HtmlString($table));
        $mail->line(new HtmlString('<br>'));

        $mail->action('Go to Dashboard', url('/formovertime'));

        return $mail->line('Please take necessary action. Thank you.');
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
