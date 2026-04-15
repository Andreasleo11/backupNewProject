<?php

namespace App\Application\PurchaseRequest\Queries\Filters;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter for PRs created by the user that are currently in the approval pipeline.
 */
final class MyActiveRequestsFilter implements PurchaseRequestFilter
{
    public function __construct(private readonly User $user) {}

    public function apply(Builder $query): void
    {
        $query->where('user_id_create', $this->user->id)
            ->inReview();
    }
}
