<?php

namespace App\Notifications;

use App\Domain\Approval\Contracts\Approvable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReportApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public bool $afterCommit = true;

    public function __construct(
        public readonly Approvable $approvable,
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
            'title' => "{$type} Approved",
            'message' => "{$type} #{$id} has been fully approved.",
            'action_url' => $this->approvable->getApprovableShowUrl(),
            'icon' => 'bx bx-check-circle',
            'category' => 'success',
            'approvable_id' => $this->approvable->id,
        ];
    }
}
