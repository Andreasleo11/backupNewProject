<?php

namespace App\Notifications;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PurchaseRequestApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Dispatch after the DB transaction commits to prevent race conditions.
     */
    public bool $afterCommit = true;

    public function __construct(
        public readonly PurchaseRequest $purchaseRequest,
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
        return [
            'title'      => 'Purchase Request Approved',
            'message'    => "Purchase Request #{$this->purchaseRequest->pr_no} has been fully approved.",
            'action_url' => route('purchase-requests.show', $this->purchaseRequest->id),
            'pr_id'      => $this->purchaseRequest->id,
        ];
    }
}
