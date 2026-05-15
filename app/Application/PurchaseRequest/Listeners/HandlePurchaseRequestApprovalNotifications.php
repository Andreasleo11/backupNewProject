<?php
namespace App\Application\PurchaseRequest\Listeners;

use App\Events\ApprovalCompleted;
use App\Models\PurchaseRequest;
use App\Notifications\PurchaseRequestApprovedNotification;
use App\Infrastructure\Approval\Services\ApprovalScopingManager;
use Illuminate\Support\Facades\Notification;

final class HandlePurchaseRequestApprovalNotifications
{
    public function __construct(private ApprovalScopingManager $scopingManager) {}

    public function handle(ApprovalCompleted $event): void
    {
        if (! $event->approvable instanceof PurchaseRequest) {
            return;
        }

        $pr = $event->approvable;

        // 1) Purchaser-related roles (purchaser, purchaser-*)
        $purchasers = \App\Infrastructure\Persistence\Eloquent\Models\User::role('purchaser')->get()
            ->filter(function ($user) use ($pr) {
                return $this->scopingManager->isUserEligible($user, 'purchaser', $pr)
                    && $this->scopingManager->wantsNotification($user, get_class($pr), 'immediate');
            });

        $watchers = [];
        // // 2) Permission-based watchers (pr.notify-approved)
        // $watchers = \App\Infrastructure\Persistence\Eloquent\Models\User::permission('pr.notify-approved')->get()->filter(function ($user) use ($pr) {
        //     return $this->scopingManager->wantsNotification($user, get_class($pr), 'immediate');
        // });

        // Merge and deduplicate by user id
        $recipients = $purchasers->merge($watchers)->unique('id')->values();

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new PurchaseRequestApprovedNotification($pr));
        }
    }
}
