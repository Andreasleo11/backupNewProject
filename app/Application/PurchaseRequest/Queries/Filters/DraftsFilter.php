<?php

namespace App\Application\PurchaseRequest\Queries\Filters;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Filter for PRs created by the user that are still in DRAFT mode.
 */
final class DraftsFilter implements PurchaseRequestFilter
{
    public function __construct(private readonly User $user) {}

    public function apply(Builder $query): void
    {
        $query->where('user_id_create', $this->user->id)
              ->where(function($q) {
                  $q->whereDoesntHave('approvalRequest')
                    ->orWhereHas('approvalRequest', fn($aq) => $aq->where('status', 'DRAFT'));
              });
    }
}
