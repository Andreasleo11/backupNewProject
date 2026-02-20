<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PurchaseRequestRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Dispatch after the DB transaction commits to prevent race conditions.
     */
    public bool $afterCommit = true;

    public function __construct(
        public readonly PurchaseRequest $purchaseRequest,
        public readonly ?string $remarks = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = "Purchase Request #{$this->purchaseRequest->pr_no} has been rejected.";

        if ($this->remarks) {
            $message .= " Reason: {$this->remarks}";
        }

        return [
            'title' => 'Purchase Request Rejected',
            'message' => $message,
            'action_url' => route('purchase-requests.show', $this->purchaseRequest->id),
            'icon' => 'bx bx-x-circle',
            'category' => 'danger',
            // domain extras
            'pr_id' => $this->purchaseRequest->id,
            'remarks' => $this->remarks,
        ];
    }
}
