<?php

namespace App\Application\PurchaseRequest\Queries\Filters;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Canonical "My Approval" filter.
 *
 * Shows only IN_REVIEW PRs whose CURRENT step is assigned to this user
 * (either directly by user ID, or via one of their roles by ID or slug).
 *
 * This is the single source of truth used by:
 *   - PurchaseRequestsDataTable  (filter=my_approval)
 *   - GetPurchaseRequestStats    (pending_my_approval card count)
 *
 * It handles both numeric role-ID assignment and legacy role-name/slug assignment.
 */
final class MyApprovalFilter implements PurchaseRequestFilter
{
    public function __construct(private readonly User $user) {}

    public function apply(Builder $query): void
    {
        $userId = $this->user->id;
        $roleIds = $this->user->roles->pluck('id')->toArray();
        $roleNames = $this->user->getRoleNames()->toArray();

        $query->inReview()
            ->whereHas('approvalRequest.steps', function ($q) use ($userId, $roleIds, $roleNames) {
                // Match the current active step only
                $q->where(
                    'sequence',
                    DB::raw('(SELECT current_step FROM approval_requests WHERE id = approval_steps.approval_request_id)')
                )
                    ->whereNull('acted_at')
                    ->where(function ($q2) use ($userId, $roleIds, $roleNames) {
                        // Directly assigned to this user
                        $q2->where(function ($u) use ($userId) {
                            $u->where('approver_type', 'user')
                                ->where('approver_id', $userId);
                        })
                        // Or assigned to one of their roles (numeric ID or slug string)
                            ->orWhere(function ($r) use ($roleIds, $roleNames) {
                                $r->where('approver_type', 'role')
                                    ->where(function ($q) use ($roleIds, $roleNames) {
                                        $q->whereIn('approver_id', $roleIds)
                                            ->orWhereIn('approver_id', $roleNames);
                                    });
                            });
                    });
            });
    }
}
