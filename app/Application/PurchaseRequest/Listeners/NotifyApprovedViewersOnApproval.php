<?php
namespace App\Application\PurchaseRequest\Listeners;

use App\Events\ApprovalCompleted;
use App\Domain\PurchaseRequest\Models\PurchaseRequest;
use App\Notifications\PurchaseRequestApprovedNotification;
use App\Infrastructure\Approval\Services\ApprovalScopingManager;
use Illuminate\Support\Facades\Notification;

class NotifyApprovedViewersOnApproval
{
    public function __construct(private ApprovalScopingManager $scopingManager) {}

    public function handle(ApprovalCompleted $event): void
    {
        // Only handle PurchaseRequest approvals
        if (! $event->approvable instanceof PurchaseRequest) {
            return;
        }

        $pr = $event->approvable;

        // Get all users with 'pr.notify-approved' permission
        $potentialUsers = \App\Infrastructure\Persistence\Eloquent\Models\User::permission('pr.notify-approved')->get();

        // Filter to users who want notifications
        $eligibleUsers = $potentialUsers->filter(function ($user) use ($pr) {
            // Check notification preferences for immediate notifications
            return $this->scopingManager->wantsNotification($user, get_class($pr), 'immediate');
        });

        if ($eligibleUsers->isNotEmpty()) {
            Notification::send($eligibleUsers, new PurchaseRequestApprovedNotification($pr));
        }
    }
}