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
        return [
            'title'      => 'Purchase Request — Action Required',
            'message'    => "Purchase Request #{$this->purchaseRequest->pr_no} is awaiting your approval (Step {$this->step->sequence}).",
            'action_url' => route('purchase-requests.show', $this->purchaseRequest->id),
            'icon'       => 'bx bx-bell-ring',
            'category'   => 'info',
            // domain extras (surfaced in meta / modal)
            'pr_id'          => $this->purchaseRequest->id,
            'step_sequence'  => $this->step->sequence,
        ];
    }
}
