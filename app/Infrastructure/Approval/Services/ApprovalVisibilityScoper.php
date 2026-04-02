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

        // --- LAYER 1: GLOBAL OVERRIDES (No Scoping) ---
        
        // 1. Super-Admin always sees everything
        if ($user->hasRole('super-admin')) {
            return;
        }

        // 2. Specialized 'View-All' Permissions
        // Note: Non-SuperAdmin View-All permissions are now handled 
        // inside the ApprovalScopingManager to allow for state-restricted oversight.

        $query->where(function ($groupedQuery) use ($user, $manager) {
            // Seed with false to ensure the group evaluates to false if no criteria match
            $groupedQuery->whereRaw('1 = 0');

            // A. Historical: User signed it
            $groupedQuery->orWhereHas('steps', function ($sq) use ($user) {
                $sq->where('acted_by', $user->id);
            });

            // B. Active Turn: Specifically for this user (User or Role match)
            $groupedQuery->orWhere(function ($activeTurnQuery) use ($user, $manager) {
                // Determine if this user's turn matches must be restricted by jurisdiction (Branch/Dept)
                // General Managers and Dept Heads are strictly local to their branches.
                $isBranchScoped = $user->hasAnyRole(['department-head', 'supervisor', 'general-manager']) && 
                                  !$user->hasRole('super-admin');

                $roleIds = $user->roles->pluck('id')->toArray();
                $roleNames = $user->getRoleNames()->toArray();
                
                $activeTurnQuery->where('status', 'IN_REVIEW')
                    ->whereHas('steps', function ($sq) use ($user, $roleIds, $roleNames) {
                        $sq->whereColumn('sequence', 'approval_requests.current_step')
                           ->where(function ($matchQuery) use ($user, $roleIds, $roleNames) {
                               // Match by User ID
                               $matchQuery->where('approver_type', 'user')
                                          ->where('approver_id', $user->id);
                               
                               // OR Match by Role (Numeric ID or Name)
                               if (!empty($roleIds)) {
                                   $matchQuery->orWhere(function($rq) use ($roleIds, $roleNames) {
                                       $rq->where('approver_type', 'role')
                                          ->where(function($q) use ($roleIds, $roleNames) {
                                              $q->whereIn('approver_id', $roleIds)
                                                ->orWhereIn('approver_id', $roleNames);
                                          });
                                   });
                               }
                           });
                    });

                // Jurisdiction check for branch-restricted roles (Intersection)
                if ($isBranchScoped) {
                    $manager->applyVisibilityScope($activeTurnQuery, $user, ['IN_REVIEW']);
                }
            });

            // C. Role-Based Oversight (Jurisdiction)
            $groupedQuery->orWhere(function ($oversightQuery) use ($user, $manager) {
                $oversightQuery->whereIn('status', ['IN_REVIEW', 'APPROVED', 'REJECTED', 'CANCELED']);
                $manager->applyVisibilityScope($oversightQuery, $user);
            });
        });
    }
}
