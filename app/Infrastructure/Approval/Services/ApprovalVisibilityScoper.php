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
    public function apply(Builder $query, User $user, bool $wideView = false): void
    {
        $manager = new ApprovalScopingManager;

        // --- LAYER 1: GLOBAL OVERRIDES (No Scoping) ---
        
        // 1. Super-Admin always sees everything
        if ($user->hasRole('super-admin')) {
            return;
        }

        // 2. Specialized 'View-All' Permissions (If Wide View is ON)
        $canGlobalView = $user->can('approval.view-all') || 
                        $user->can('overtime.view-all') || 
                        $user->can('purchase-request.view-all');

        if ($wideView && $canGlobalView) {
            // Note: If a GM has view-all, they override their branch restriction in Wide View.
            return;
        }

        // --- LAYER 2: SCOPED VISIBILITY (Jurisdictional) ---

        // Determine if this is a privileged user (Director, GM, or has view-all) 
        // to handle the Action-Only vs Wide View UI logic.
        $isPrivileged = $user->hasAnyRole(['director', 'general-manager']) || $canGlobalView;
        $isFocusedMode = !$wideView && $isPrivileged;

        $query->where(function ($groupedQuery) use ($user, $manager, $wideView, $isFocusedMode) {
            // Seed with false to ensure the group evaluates to false if no criteria match
            $groupedQuery->whereRaw('1 = 0');

            // A. Historical: User signed it
            // Enabled if: Wide View OR not in Focused Mode (General users see their own)
            if (!$isFocusedMode) {
                $groupedQuery->orWhereHas('steps', function ($sq) use ($user) {
                    $sq->where('acted_by', $user->id);
                });
            }

            // B. Active Turn: Specifically for this user (User or Role match)
            // Always enabled in all modes.
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
            // Enabled ONLY in Wide View for privileged users.
            if (!$isFocusedMode) {
                $groupedQuery->orWhere(function ($oversightQuery) use ($user, $manager) {
                    $oversightQuery->whereIn('status', ['IN_REVIEW', 'APPROVED', 'REJECTED']);
                    $manager->applyVisibilityScope($oversightQuery, $user);
                });
            }
        });
    }
}
