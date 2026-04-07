<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\Queries;

use App\Application\PurchaseRequest\Queries\Filters\ApprovedThisMonthFilter;
use App\Application\PurchaseRequest\Queries\Filters\InReviewFilter;
use App\Application\PurchaseRequest\Queries\Filters\MyApprovalFilter;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\Cache;

class GetPurchaseRequestStats
{
    public function __construct(
        private readonly PurchaseRequestQueryBuilder $queryBuilder
    ) {}

    /**
     * Get PR statistics for the current user
     */
    public function execute(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        $userId = $user->id;
        $cacheKey = "pr_stats_user_{$userId}";

        // Lower cache duration to 2 minutes for better responsiveness while protecting DB
        return Cache::remember($cacheKey, now()->addMinutes(2), function () use ($user) {
            return [
                'pending_my_approval' => $this->getPendingMyApproval($user),
                'in_review' => $this->getInReview($user),
                'approved_this_month' => $this->getApprovedThisMonth($user),
                'total_value_pending' => $this->getTotalValuePending($user),
            ];
        });
    }

    /**
     * Get count of PRs pending current user's approval
     */
    private function getPendingMyApproval(User $user): int
    {
        $query = $this->queryBuilder->forUser($user);
        
        // Use the canonical MyApprovalFilter
        (new MyApprovalFilter($user))->apply($query);

        return $query->count();
    }

    /**
     * Get count of all PRs in review visible to this user
     */
    private function getInReview(User $user): int
    {
        $query = $this->queryBuilder->forUser($user);
        
        // Use the canonical InReviewFilter
        (new InReviewFilter())->apply($query);

        return $query->count();
    }

    /**
     * Get count of PRs approved this month visible to this user
     */
    private function getApprovedThisMonth(User $user): int
    {
        $query = $this->queryBuilder->forUser($user);
        
        // Use the canonical ApprovedThisMonthFilter
        (new ApprovedThisMonthFilter())->apply($query);

        return $query->count();
    }

    /**
     * Get total value of pending PRs grouped by currency visible to this user
     */
    private function getTotalValuePending(User $user): array
    {
        $totals = [];

        $query = $this->queryBuilder->forUser($user);
        
        // Only count In-Review PRs for value pending
        (new InReviewFilter())->apply($query);

        $prs = $query->with('items')->get();

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

        // Ensure IDR: 0 is returned if nothing exists
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
        $userId = $userId ?? (int) auth()->id();
        if ($userId) {
            Cache::forget("pr_stats_user_{$userId}");
        }
    }
}
