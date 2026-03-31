<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class ApprovalSummaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param Collection<\App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest> $pendingRequests
     */
    public function __construct(
        public readonly Collection $pendingRequests
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->pendingRequests->count();
        $message = (new MailMessage)
            ->subject("Daily Approval Summary: {$count} Pending Requests")
            ->greeting("Hello, {$notifiable->name}")
            ->line("You have {$count} requests awaiting your approval across the system.")
            ->line("Below is a summary of your pending tasks:");

        // Build a simple list of the requests
        foreach ($this->pendingRequests as $request) {
            $approvable = $request->approvable;
            if (!$approvable) continue;

            $type = $approvable->getApprovableTypeLabel();
            $id = $approvable->getApprovableIdentifier();
            $url = $approvable->getApprovableShowUrl();

            $message->line("- [{$type} #{$id}]({$url}) (Status: {$request->status})");
        }

        return $message
            ->action('View All Approvals', route('approvals'))
            ->line('Thank you for your prompt attention to these requests.');
    }
}
