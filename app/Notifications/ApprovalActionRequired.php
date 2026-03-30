<?php

namespace App\Notifications;

use App\Domain\Approval\Contracts\Approvable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ApprovalActionRequired extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Approvable $approvable,
        public readonly mixed $step
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $type = $this->approvable->getApprovableTypeLabel();
        $id = $this->approvable->getApprovableIdentifier();
        $url = $this->approvable->getApprovableShowUrl();

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject("Action Required: {$type} #{$id}")
            ->greeting("Hello, {$notifiable->name}")
            ->line("A {$type} (#{$id}) is awaiting your approval.")
            ->line("Current Step: Step {$this->step->sequence} ({$this->step->approver_snapshot_label})")
            ->action('View Request', $url)
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        $type = $this->approvable->getApprovableTypeLabel();
        $id = $this->approvable->getApprovableIdentifier();

        return [
            'title' => "{$type} — Action Required",
            'message' => "{$type} #{$id} is awaiting your approval (Step {$this->step->sequence}).",
            'action_url' => $this->approvable->getApprovableShowUrl(),
            'icon' => 'bx bx-bell-ring',
            'category' => 'info',
            'approvable_id' => $this->approvable instanceof \Illuminate\Database\Eloquent\Model ? $this->approvable->getKey() : null,
            'step_sequence' => $this->step->sequence,
        ];
    }
}
