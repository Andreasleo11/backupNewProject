<?php

namespace App\Application\PurchaseRequest\Services;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class PurchaseRequestQueryScoper
{
    /**
     * Apply centralized, strict visibility scoping to the Purchase Request query.
     * Uses the unified ApprovalVisibilityScoper for all role and turn-based logic.
     */
    public function scopeForUser(User $user, Builder $query): Builder
    {
        // 1. Super Admin: View All
        if ($user->hasRole('super-admin')) {
            return $query;
        }

        return $query->where(function ($groupedQuery) use ($user) {
            // A. Identity: Always sees own PRs as creator
            $groupedQuery->orWhere('user_id_create', $user->id);

            // B. Everything else: Use Centralized Approval Scoper 
            // Handles Signed history, Active Turns, Specialized Roles (Purchaser), Branch scoping (GM), etc.
            $groupedQuery->orWhereHas('approvalRequest', function ($aq) use ($user) {
                $aq->forUser($user);
            });
        });
    }
}
