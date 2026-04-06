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
        
        // 1. Admins with global bypass always see everything
        if ($user->can('system.admin') || $user->can('pr.admin')) {
            return;
        }

        // 2. Specialized 'View-All' Permissions
        // Note: Non-Admin View-All permissions are handled 
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
                $isBranchScoped = $user->hasAnyRole(config('approvals.jurisdiction_scoped_roles', ['department-head', 'supervisor', 'general-manager'])) 
                                  && !$user->can('system.admin') 
                                  && !$user->can('pr.admin');

                $roleIds = $user->roles->pluck('id')->toArray();
                $roleNames = $user->getRoleNames()->toArray();
                
                $activeTurnQuery->where('status', 'IN_REVIEW')
                    ->where(function ($matchGroup) use ($user, $roleIds, $roleNames, $isBranchScoped, $manager) {
                        // 1. Must match the User, or one of their Roles
                        $matchGroup->whereHas('steps', function ($sq) use ($user, $roleIds, $roleNames) {
                            $sq->whereColumn('sequence', 'approval_requests.current_step')
                               ->where(function ($matchQuery) use ($user, $roleIds, $roleNames) {
                                   $matchQuery->where('approver_type', 'user')
                                              ->where('approver_id', $user->id);
                                   
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

                        // 2. AND if branch-scoped, MUST also match the jurisdiction
                        if ($isBranchScoped) {
                            $manager->applyVisibilityScope($matchGroup, $user, ['IN_REVIEW']);
                        }
                    });
            });

            // C. Role-Based Oversight (Jurisdiction)
            $groupedQuery->orWhere(function ($oversightQuery) use ($user, $manager) {
                $oversightQuery->whereIn('status', ['IN_REVIEW', 'APPROVED', 'REJECTED', 'CANCELED']);
                $manager->applyVisibilityScope($oversightQuery, $user);
            });
        });
    }
}
