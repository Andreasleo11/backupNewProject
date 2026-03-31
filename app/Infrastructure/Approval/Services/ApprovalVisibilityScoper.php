<?php

namespace App\Infrastructure\Approval\Services;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Centralized "Source of Truth" for determining which users see which approval requests.
 * Mirror's the ApprovalEngine's decision tree to ensure consistency.
 */
class ApprovalVisibilityScoper
{
    /**
     * Apply strict visibility rules to an ApprovalRequest query.
     */
    public function apply(Builder $query, User $user): void
    {
        $manager = new ApprovalScopingManager;

        // 1. super-admin OR specialized view-all permission
        if ($user->hasRole('super-admin') || $user->can('approval.view-all')) {
            return;
        }

        $query->where(function ($groupedQuery) use ($user, $manager) {
            // Seed with false to ensure the group evaluates to false if no criteria match
            $groupedQuery->whereRaw('1 = 0');

            // A. Historical: User signed it
            $groupedQuery->orWhereHas('steps', function ($sq) use ($user) {
                $sq->where('acted_by', $user->id);
            });

            // B. Active Turn: specifically for this user (User ID match)
            // Note: Role-based turns are handled by the Oversight/Jurisdiction block below
            // to ensure specialized roles (Dept Head/GM/Purchaser) always match their scope.
            $groupedQuery->orWhere(function ($activeTurnQuery) use ($user) {
                $activeTurnQuery->where('status', 'IN_REVIEW')
                    ->whereHas('steps', function ($sq) use ($user) {
                        $sq->whereColumn('sequence', 'approval_requests.current_step')
                           ->where('approver_type', 'user')
                           ->where('approver_id', $user->id);
                    });
            });

            // C. Role-Based Oversight (Jurisdiction)
            // Delegates to ApprovalScopingManager to ensure linked depts and 
            // branch scopes are respected. Covers IN_REVIEW, APPROVED, REJECTED.
            $groupedQuery->orWhere(function ($oversightQuery) use ($user, $manager) {
                $oversightQuery->whereIn('status', ['IN_REVIEW', 'APPROVED', 'REJECTED']);
                $manager->applyVisibilityScope($oversightQuery, $user);
            });
        });
    }
}
