<?php
namespace App\Application\PurchaseRequest\Listeners;

use App\Events\ApprovalCompleted;
use App\Models\PurchaseRequest;
use App\Notifications\PurchaseRequestApprovedNotification;
use App\Infrastructure\Approval\Services\ApprovalScopingManager;
use Illuminate\Support\Facades\Notification;

class NotifyPurchasersOnApproval
{
    public function __construct(private ApprovalScopingManager $scopingManager) {}

    public function handle(ApprovalCompleted $event): void
    {
        // Only handle PurchaseRequest approvals
        if (! $event->approvable instanceof PurchaseRequest) {
            return;
        }

        $pr = $event->approvable;

        // Get all users with 'purchaser' role
        $potentialPurchasers = \App\Infrastructure\Persistence\Eloquent\Models\User::role('purchaser')->get();

        // Filter to eligible purchasers based on department specialization and notification preferences
        $eligiblePurchasers = $potentialPurchasers->filter(function ($user) use ($pr) {
            // 1. Check jurisdictional eligibility (department/branch scoping)
            if (! $this->scopingManager->isUserEligible($user, 'purchaser', $pr)) {
                return false;
            }

            // 2. Check notification preferences for immediate notifications
            return $this->scopingManager->wantsNotification($user, get_class($pr), 'immediate');
        });

        if ($eligiblePurchasers->isNotEmpty()) {
            Notification::send($eligiblePurchasers, new PurchaseRequestApprovedNotification($pr));
        }
    }
}