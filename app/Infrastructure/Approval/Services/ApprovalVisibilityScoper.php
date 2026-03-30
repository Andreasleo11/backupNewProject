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

        // 1. Super Admin OR Global View Permission: View All
        if ($user->hasRole('super-admin') || $user->can('approval.view-all')) {
            return;
        }

        $query->where(function ($groupedQuery) use ($user, $manager) {
            // 2. Multi-Criteria Visibility (User sees anything matching ANY of these)

            // A. Historical: User signed it
            $groupedQuery->orWhereHas('steps', function ($sq) use ($user) {
                $sq->where('acted_by', $user->id);
            });

            // B. Active Turn: specifically for this user
            $groupedQuery->orWhere(function ($activeTurnQuery) use ($user, $manager) {
                $activeTurnQuery->where('status', 'IN_REVIEW')
                    ->whereHas('steps', function ($sq) use ($user) {
                        $sq->whereColumn('sequence', 'approval_requests.current_step')
                           ->where(function ($match) use ($user) {
                               // Specific User ID
                               $match->where(function ($uMatch) use ($user) {
                                   $uMatch->where('approver_type', 'user')
                                          ->where('approver_id', $user->id);
                               })
                               // Or Role (subject to specialized scoping)
                               ->orWhere(function ($rMatch) use ($user) {
                                   $rMatch->where('approver_type', 'role')
                                          ->whereIn('approver_id', $user->roles->pluck('id')->toArray());
                               });
                           });
                    });

                // C. ADAPTIVE SCOPING FOR ROLES (The Decision Tree)
                // Even if it matches the Role ID, we further restrict by Dept/Branch for specific key roles.
                // This logic mirrors ApprovalEngine::notifyCurrentApprover via ApprovalScopingManager
                $activeTurnQuery->where(function ($strictQuery) use ($user, $manager) {
                    // Check specialized Purchaser role
                    if ($user->hasRole('purchaser')) {
                        $depts = $manager->getPurchaserSpecializedDepartments($user);
                        if (!empty($depts)) {
                            $strictQuery->orWhereHasMorph('approvable', '*', function ($aq) use ($depts) {
                                $aq->whereIn('to_department', $depts);
                            });
                        }
                    }

                    // Check GM (Branch Match)
                    if ($user->hasRole('general-manager')) {
                        $branch = strtoupper(trim((string)($user->employee->branch ?? '')));
                        if ($branch) {
                            $strictQuery->orWhereHasMorph('approvable', '*', function ($aq) use ($branch) {
                                $aq->where('branch', $branch);
                            });
                        }
                    }

                    // Check Dept Head (Origination Match with Linkages from Manager)
                    if ($user->hasRole('department-head')) {
                        $eligibleDepts = $manager->getEligibleDepartments($user);
                        $deptId = $user->department_id;

                        if (!empty($eligibleDepts) || $deptId) {
                            $strictQuery->orWhereHasMorph('approvable', '*', function ($aq) use ($eligibleDepts, $deptId) {
                                $aq->where(function ($sq) use ($eligibleDepts, $deptId) {
                                    if (!empty($eligibleDepts)) $sq->orWhereIn('from_department', $eligibleDepts);
                                    if ($deptId) $sq->orWhere('dept_id', $deptId); // for OT which uses FK
                                });
                            });
                        }
                    }

                    // Global Oversight (Director sees Active tasks globally)
                    if ($user->hasRole('director')) {
                         $strictQuery->orWhereRaw('1=1');
                    }
                });
            });

            // D. Finalized Visibility (Director sees all closed)
            if ($user->hasRole('director')) {
                $groupedQuery->orWhereIn('status', ['APPROVED', 'REJECTED']);
            }
        });
    }

    /**
     * For backward compatibility if needed, but logic is now in ScopingManager.
     */
    private function getPurchaserSpecializedDepartments(User $user): array
    {
        return (new ApprovalScopingManager)->getPurchaserSpecializedDepartments($user);
    }
}
