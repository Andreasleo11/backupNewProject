<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\Queries;

use App\Application\PurchaseRequest\Queries\Filters\ApprovedThisMonthFilter;
use App\Application\PurchaseRequest\Queries\Filters\DeptActiveRequestsFilter;
use App\Application\PurchaseRequest\Queries\Filters\DraftsFilter;
use App\Application\PurchaseRequest\Queries\Filters\InReviewFilter;
use App\Application\PurchaseRequest\Queries\Filters\MyActiveRequestsFilter;
use App\Application\PurchaseRequest\Queries\Filters\MyApprovalFilter;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Facades\DB;

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

        $isStrategic = $user->hasAnyRole(['director', 'super-admin', 'purchasing-manager']);
        $isVerificator = $user->hasRole('verificator');

        // 1. Always show Pending My Approval (Workload)
        $stats = [
            'pending_my_approval' => $this->getPendingMyApproval($user),
        ];

        if ($isStrategic) {
            // Strategic roles see global metrics
            $stats['in_review'] = $this->getInReview($user);
            $stats['approved_this_month'] = $this->getApprovedThisMonth($user);
            $stats['total_value_pending'] = $this->getTotalValuePending($user);
        } else {
            // Operational roles see contextual metrics
            $stats['my_active'] = $this->getMyActiveCount($user);

            if (! $isVerificator) {
                $stats['dept_active'] = $this->getDeptActiveCount($user);
            }

            $stats['drafts'] = $this->getDraftsCount($user);
        }

        return $stats;
    }

    /**
     * Get count of PRs pending current user's approval
     */
    private function getPendingMyApproval($user): int
    {
        $query = $this->queryBuilder->forUser($user);
        (new MyApprovalFilter($user))->apply($query);

        return $query->count();
    }

    /**
     * Get count of PRs created by the user that are currently in review
     */
    private function getMyActiveCount($user): int
    {
        $query = $this->queryBuilder->forUser($user);
        (new MyActiveRequestsFilter($user))->apply($query);

        return $query->count();
    }

    /**
     * Get count of PRs in the user's department that are currently in review
     */
    private function getDeptActiveCount($user): int
    {
        $query = $this->queryBuilder->forUser($user);
        (new DeptActiveRequestsFilter($user))->apply($query);

        return $query->count();
    }

    /**
     * Get count of drafts created by the user
     */
    private function getDraftsCount($user): int
    {
        $query = $this->queryBuilder->forUser($user);
        (new DraftsFilter($user))->apply($query);

        return $query->count();
    }

    /**
     * Get count of all PRs in review visible to this user
     */
    private function getInReview($user): int
    {
        $query = $this->queryBuilder->forUser($user);
        (new InReviewFilter)->apply($query);

        return $query->count();
    }

    /**
     * Get count of PRs approved this month visible to this user
     */
    private function getApprovedThisMonth($user): int
    {
        $query = $this->queryBuilder->forUser($user);
        (new ApprovedThisMonthFilter)->apply($query);

        return $query->count();
    }

    /**
     * Get total value of pending PRs grouped by currency visible to this user
     */
    private function getTotalValuePending($user): array
    {
        // Build the base query for visibility scoping
        $baseQuery = $this->queryBuilder->forUser($user);
        (new InReviewFilter)->apply($baseQuery);

        // Get the IDs of visible PRs first
        $prIds = $baseQuery->pluck('purchase_requests.id');

        // Now do the aggregation on a fresh query
        $totals = DB::table('detail_purchase_requests')
            ->whereIn('purchase_request_id', $prIds)
            ->selectRaw('UPPER(COALESCE(currency, \'IDR\')) as currency, SUM(COALESCE(quantity, 0) * COALESCE(price, 0)) as total_value')
            ->groupBy('currency')
            ->pluck('total_value', 'currency')
            ->toArray();

        return ! empty($totals) ? array_map('floatval', $totals) : ['IDR' => 0.0];
    }
}
