<?php

// app/Infrastructure/Notifications/ReportRejected.php

namespace App\Infrastructure\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private $report) {}

    public function via($notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Report {$this->report->document_number} Rejected")
            ->greeting('Hi!')
            ->line("Your report **{$this->report->title}** has been rejected.")
            ->action('Open Report', route('verification.show', $this->report->id));
    }

    public function toArray($notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'document_number' => $this->report->document_number,
            'status' => $this->report->status,
        ];
    }
}
