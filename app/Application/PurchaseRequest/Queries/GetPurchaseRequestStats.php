<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\Queries;

use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class GetPurchaseRequestStats
{
    /**
     * Get PR statistics for the current user
     */
    public function execute(): array
    {
        $userId = auth()->id();
        $cacheKey = "pr_stats_user_{$userId}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () {
            return [
                'pending_my_approval' => $this->getPendingMyApproval(),
                'in_review' => $this->getInReview(),
                'approved_this_month' => $this->getApprovedThisMonth(),
                'total_value_pending' => $this->getTotalValuePending(),
            ];
        });
    }

    /**
     * Get count of PRs pending current user's approval (checking both User ID and Role IDs)
     */
    private function getPendingMyApproval(): int
    {
        $userId = auth()->id();
        $roleIds = auth()->user()->roles->pluck('id')->toArray();

        return PurchaseRequest::query()
            ->inReview()  // Use new query scope
            ->whereHas('approvalRequest.steps', function ($q) use ($userId, $roleIds) {
                // Match the current step sequence
                $q->where('sequence', DB::raw('(SELECT current_step FROM approval_requests WHERE id = approval_steps.approval_request_id)'))
                    ->whereNull('acted_at')
                    ->where(function ($query) use ($userId, $roleIds) {
                        // Check if assigned strictly to the User
                        $query->where(function ($u) use ($userId) {
                            $u->where('approver_type', 'user')
                                ->where('approver_id', $userId);
                        })
                        // Or assigned to a Role the User currently has
                            ->orWhere(function ($r) use ($roleIds) {
                                $r->where('approver_type', 'role')
                                    ->whereIn('approver_id', $roleIds);
                            });
                    });
            })
            ->count();
    }

    /**
     * Get count of all PRs in review
     */
    private function getInReview(): int
    {
        return PurchaseRequest::inReview()->count();  // Use query scope
    }

    /**
     * Get count of PRs approved this month
     */
    private function getApprovedThisMonth(): int
    {
        return PurchaseRequest::query()
            ->workflowApproved()  // Use query scope
            ->whereYear('updated_at', now()->year)
            ->whereMonth('updated_at', now()->month)
            ->count();
    }

    /**
     * Get total value of pending PRs grouped by currency
     */
    private function getTotalValuePending(): array
    {
        $totals = [];

        $prs = PurchaseRequest::query()
            ->inReview()  // Use query scope
            ->with('items')
            ->get();

        foreach ($prs as $pr) {
            foreach ($pr->items as $item) {
                $currency = strtoupper($item->currency ?? 'IDR');
                $value = ($item->quantity ?? 0) * ($item->price ?? 0);

                if (! isset($totals[$currency])) {
                    $totals[$currency] = 0.0;
                }
                $totals[$currency] += $value;
            }
        }

        // Ensure 0 is returned if nothing exists
        if (empty($totals)) {
            return ['IDR' => 0];
        }

        return $totals;
    }

    /**
     * Clear stats cache for a user
     */
    public static function clearCache(?int $userId = null): void
    {
        $userId = $userId ?? auth()->id();
        Cache::forget("pr_stats_user_{$userId}");
    }
}
