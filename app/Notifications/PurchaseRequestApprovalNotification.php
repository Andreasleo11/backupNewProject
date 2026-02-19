<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PurchaseRequestApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Dispatch the notification only after the DB transaction commits.
     * This prevents race conditions when called from within ApprovalEngine transactions.
     */
    public bool $afterCommit = true;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public $purchaseRequest,
        public $step
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $pr = $this->purchaseRequest;
        $step = $this->step;

        return [
            'title' => 'Approval Required',
            'message' => "Purchase Request #{$pr->pr_no} requires your approval as {$step->approver_label}.",
            'action_url' => route('purchase-requests.show', $pr->id),
            'pr_id' => $pr->id,
            'step_sequence' => $step->sequence,
        ];
    }
}
