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

        // 1. Super-admin always sees everything (Wide View by default)
        if ($user->hasRole('super-admin')) {
            return;
        }

        // 2. Specialized view-all permission ONLY if Wide View is toggled ON
        if ($wideView && ($user->can('approval.view-all') || $user->can('overtime.view-all'))) {
            return;
        }

        // Determine if this is a privileged user currently in "Focused Mode"
        $isPrivileged = $user->hasAnyRole(['super-admin', 'director', 'general-manager']) || 
                        $user->can('approval.view-all') || 
                        $user->can('overtime.view-all') ||
                        $user->can('purchase-request.view-all');
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
                // If the user has a department-scoped role, we must enforce jurisdiction 
                // throughout the 'In Review' process (active turn).
                $isDeptScoped = $user->hasAnyRole(['department-head', 'supervisor']) && 
                                !$user->hasAnyRole(['director', 'general-manager', 'super-admin']);

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

                // Jurisdiction check for departmental roles (Intersection)
                if ($isDeptScoped) {
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
