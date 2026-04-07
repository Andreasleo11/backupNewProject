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
                // We skip this restriction if they have ANY module-specific "view-all" or "admin" permission.
                $isBranchScoped = $user->hasAnyRole(config('approvals.jurisdiction_scoped_roles', ['department-head', 'supervisor', 'general-manager'])) 
                                  && !$user->can('system.admin') 
                                  && !$user->can('pr.admin')
                                  && !$user->can('pr.view-all')
                                  && !$user->can('overtime.view-all')
                                  && !$user->can('approval.view-all');

                $roleIds = $user->roles->pluck('id')->toArray();
                $roleNames = $user->getRoleNames()->toArray();
                
                // Identify the specific 'purchaser' role to exclude it from the global 'match-all'
                $purchaserRole = $user->roles->firstWhere('name', 'purchaser');
                $otherRoleIds = $purchaserRole ? array_diff($roleIds, [$purchaserRole->id]) : $roleIds;
                $otherRoleNames = array_diff($roleNames, ['purchaser']);

                $activeTurnQuery->where('status', 'IN_REVIEW')
                    ->where(function ($matchGroup) use ($user, $otherRoleIds, $otherRoleNames, $purchaserRole, $isBranchScoped, $manager) {
                        // 1. Must match the User, or one of their Roles
                        $matchGroup->whereHas('steps', function ($sq) use ($user, $otherRoleIds, $otherRoleNames, $purchaserRole, $manager) {
                            $sq->whereColumn('sequence', 'approval_requests.current_step')
                               ->where(function ($matchQuery) use ($user, $otherRoleIds, $otherRoleNames, $purchaserRole, $manager) {
                                   // 1. User-specific match
                                   $matchQuery->where('approver_type', 'user')
                                              ->where('approver_id', $user->id);
                                   
                                   // 2. Role-specific match with dynamic "Strict Filtering" for purchasers
                                   if (!empty($otherRoleIds) || $purchaserRole) {
                                       $matchQuery->orWhere(function($rq) use ($user, $otherRoleIds, $otherRoleNames, $purchaserRole, $manager) {
                                           $rq->where('approver_type', 'role')
                                              ->where(function($q) use ($user, $otherRoleIds, $otherRoleNames, $purchaserRole, $manager) {
                                                  // A. Standard Match: Non-purchaser roles match globally as usual
                                                  if (!empty($otherRoleNames)) {
                                                      $q->orWhereIn('approver_id', $otherRoleNames)
                                                        ->orWhereIn('approver_id', $otherRoleIds);
                                                  }

                                                  // B. Dynamic Match: 'purchaser' role is filtered by category specialization
                                                  if ($purchaserRole) {
                                                      $q->orWhere(function ($pq) use ($user, $manager, $purchaserRole) {
                                                          // Matches logical name, slug, or the specific ID
                                                          $pq->where(function($fq) use ($purchaserRole) {
                                                              $fq->whereIn('approver_id', ['purchaser', $purchaserRole->id])
                                                                 ->orWhere('approver_snapshot_role_slug', 'purchaser');
                                                          });

                                                          $specialDepts = $manager->getPurchaserSpecializedDepartments($user);
                                                          if (!empty($specialDepts)) {
                                                              // STRICT: Only show PRs that match their categories
                                                              $pq->whereHas('request', function ($q) use ($specialDepts) {
                                                                  $q->whereHasMorph('approvable', [\App\Models\PurchaseRequest::class], function ($query) use ($specialDepts) {
                                                                      $query->whereIn('to_department', $specialDepts);
                                                                  });
                                                              });
                                                          }
                                                          // Note: If no specialDepts exist, they remain a "Global Purchaser"
                                                      });
                                                  }
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
