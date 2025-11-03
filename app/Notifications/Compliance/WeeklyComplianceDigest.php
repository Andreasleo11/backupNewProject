<?php

namespace App\Notifications\Compliance;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class WeeklyComplianceDigest extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Collection  $rows  // collection of ['name','code','percent'] (already filtered)
     */
    public function __construct(
        public Collection $rows,
        public int $threshold,
        public ?string $dashboardUrl = null,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $count = $this->rows->count();
        $mail = (new MailMessage)
            ->subject("Weekly Compliance Digest — {$count} dept(s) below {$this->threshold}%")
            ->greeting('Hi team,')
            ->line("Here is the weekly compliance digest. The following departments are below {$this->threshold}%.");

        if ($count === 0) {
            $mail->line('Great news! No departments are below the threshold this week.');
        } else {
            $this->rows->each(function ($r) use ($mail) {
                $code = $r['code'] ?? '—'; // compute outside the string
                $mail->line("- **{$r['name']}** ({$code}) — {$r['percent']}%");
            });

        }

        if ($this->dashboardUrl) {
            $mail->action('Open Compliance Dashboard', $this->dashboardUrl);
        }

        return $mail->line('— Automated weekly digest');
    }
}
