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
        return ['database'];
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
            'approvable_id' => $this->approvable->getKey(),
            'step_sequence' => $this->step->sequence,
        ];
    }
}
