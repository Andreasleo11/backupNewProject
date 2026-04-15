<?php

namespace App\Application\PurchaseRequest\Queries\Filters;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter for PRs within the user's department that are currently in review.
 */
final class DeptActiveRequestsFilter implements PurchaseRequestFilter
{
    public function __construct(private readonly User $user) {}

    public function apply(Builder $query): void
    {
        $dept = (string) ($this->user->department?->name ?? $this->user->employee?->department?->name);

        if ($dept) {
            $query->where('to_department', $dept)->inReview();
        } else {
            // If no department found, force empty result for safety
            $query->whereRaw('1 = 0');
        }
    }
}
