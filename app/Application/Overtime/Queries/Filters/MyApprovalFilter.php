<?php

namespace App\Application\Overtime\Queries\Filters;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Filter to strictly isolate Overtime Forms where the CURRENT action/step
 * is actively assigned to the logged-in user (or one of their roles).
 */
class MyApprovalFilter implements OvertimeFilter
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
                                        if (! empty($roleIds)) {
                                            $q->whereIn('approver_id', $roleIds);
                                        }
                                        if (! empty($roleNames)) {
                                            $q->orWhereIn('approver_id', $roleNames);
                                        }
                                    });
                            });
                    });
            });
    }
}
