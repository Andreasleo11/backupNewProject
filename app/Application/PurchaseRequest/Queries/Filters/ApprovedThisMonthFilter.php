<?php

namespace App\Application\PurchaseRequest\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Preset filter: show only PRs approved in the current calendar month.
 */
final class ApprovedThisMonthFilter implements PurchaseRequestFilter
{
    public function apply(Builder $query): void
    {
        $query->workflowApproved()
            ->whereYear('purchase_requests.updated_at', now()->year)
            ->whereMonth('purchase_requests.updated_at', now()->month);
    }
}
