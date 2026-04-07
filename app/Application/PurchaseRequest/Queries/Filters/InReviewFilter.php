<?php

namespace App\Application\PurchaseRequest\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Preset filter: show only IN_REVIEW PRs.
 */
final class InReviewFilter implements PurchaseRequestFilter
{
    public function apply(Builder $query): void
    {
        $query->inReview();
    }
}
