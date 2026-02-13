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
     * Get count of PRs pending current user's approval
     */
    private function getPendingMyApproval(): int
    {
        return PurchaseRequest::query()
            ->inReview()  // Use new query scope
            ->whereHas('approvalRequest.steps', function ($q) {
                $q->where('sequence', DB::raw('(SELECT current_step FROM approval_requests WHERE id = approval_steps.approval_request_id)'))
                    ->where('approver_id', auth()->id())
                    ->whereNull('acted_at');
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
            ->whereYear('approved_at', now()->year)
            ->whereMonth('approved_at', now()->month)
            ->count();
    }

    /**
     * Get total value of pending PRs (rough estimate)
     */
    private function getTotalValuePending(): float
    {
        $total = PurchaseRequest::query()
            ->inReview()  // Use query scope
            ->with('items')
            ->get()
            ->sum(function ($pr) {
                return $pr->items->sum(function ($item) {
                    return ($item->quantity ?? 0) * ($item->unit_price ?? 0);
                });
            });

        return round($total, 2);
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
