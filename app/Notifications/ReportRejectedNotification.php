<?php

namespace App\Notifications;

use App\Domain\Approval\Contracts\Approvable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReportRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Approvable $approvable,
        public readonly ?string $remarks = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $type = $this->approvable->getApprovableTypeLabel();
        $id = $this->approvable->getApprovableIdentifier();
        $reason = $this->remarks ? " Reason: {$this->remarks}" : "";

        return [
            'title' => "{$type} Rejected",
            'message' => "{$type} #{$id} has been rejected.{$reason}",
            'action_url' => $this->approvable->getApprovableShowUrl(),
            'icon' => 'bx bx-x-circle',
            'category' => 'danger',
            'approvable_id' => $this->approvable instanceof \Illuminate\Database\Eloquent\Model ? $this->approvable->getKey() : null,
        ];
    }
}
