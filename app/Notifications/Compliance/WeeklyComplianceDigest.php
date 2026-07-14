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
     * @param Collection $rows // collection of ['name','code','percent'] (already filtered)
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
        $channels = ['mail'];
        if (config('services.slack.webhook_url') || method_exists($notifiable, 'routeNotificationForSlack')) {
            $channels[] = 'slack';
        }

        return $channels;
    }

    public function toSlack(object $notifiable): \Illuminate\Notifications\Messages\SlackMessage
    {
        $count = $this->rows->count();
        $message = (new \Illuminate\Notifications\Messages\SlackMessage)
            ->info()
            ->content("📊 Weekly Compliance Digest: There are *{$count}* department(s) below the {$this->threshold}% threshold.");

        if ($count > 0) {
            $attachmentContent = "";
            $this->rows->each(function ($r) use (&$attachmentContent) {
                $code = $r['code'] ?? '—';
                $attachmentContent .= "• *{$r['name']}* ({$code}) — {$r['percent']}%\n";
            });
            $message->attachment(fn ($attachment) => $attachment->color('#36a64f')->text(rtrim($attachmentContent)));
        }

        if ($this->dashboardUrl) {
            $message->attachment(fn ($attachment) => $attachment->title('View Compliance Dashboard', $this->dashboardUrl));
        }

        return $message;
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
