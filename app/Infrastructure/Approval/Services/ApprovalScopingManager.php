<?php

namespace App\Infrastructure\Approval\Services;

use App\Domain\Approval\Contracts\Approvable;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Support\Str;

/**
 * The Single Source of Truth for all approval-related scoping (Visibility & Notifications).
 * Handles Linked Departments, Specialized Roles, and Branch Matching.
 */
class ApprovalScopingManager
{
    /**
     * Get all departments (primary + linked) that a user is responsible for.
     * Reads from config/approvals.php → department_links.
     */
    public function getEligibleDepartments(User $user): array
    {
        $baseDeptName = $user->employee?->department?->name ?? '';
        $linkedDepts  = config('approvals.department_links.' . $baseDeptName, []);

        return array_filter(array_merge([$baseDeptName], $linkedDepts));
    }

    /**
     * Determine if a specific user matches the required scope for a given role/approvable.
     * Used by the Notification Engine.
     */
    public function isUserEligible(User $user, string $roleSlug, Approvable $approvable): bool
    {
        // 1. Department Head / Supervisor (Origination Match)
        if (in_array($roleSlug, ['department-head', 'supervisor'])) {
            $formDept = $approvable->getApprovableDepartmentName();
            if (!$formDept) return false;

            $eligibleDepts = $this->getEligibleDepartments($user);
            return in_array($formDept, $eligibleDepts);
        }

        // 2. General Manager (Branch Match)
        if ($roleSlug === 'general-manager') {
            $formBranch = $approvable->getApprovableBranchValue();
            if (!$formBranch) return false;

            $userBranch = $user->employee->branch ?? '';
            return strtolower(trim((string)$userBranch)) === strtolower(trim((string)$formBranch));
        }

        // 3. Purchaser (Specialized Department Role Match)
        if ($roleSlug === 'purchaser' && $approvable instanceof \App\Models\PurchaseRequest) {
            if (!$approvable->to_department) return false;
            
            $targetRole = 'purchaser-' . Str::slug($approvable->to_department->label());
            // Support both new slug and legacy prefix if needed
            return $user->hasRole($targetRole) || $user->hasRole('purchaser-' . str_replace('purchaser-', '', $targetRole));
        }

        // 4. Global Roles (Director, Verificator, etc.) - Global Match
        return true;
    }

    /**
     * Get specialized purchaser departments for query scoping.
     */
    public function getPurchaserSpecializedDepartments(User $user): array
    {
        $targetDepartments = [];
        foreach ($user->getRoleNames() as $role) {
            if (str_starts_with($role, 'purchaser-')) {
                $slug = str_replace('purchaser-', '', $role);
                $dept = \App\Enums\ToDepartment::tryFromSlug($slug);
                if ($dept) $targetDepartments[] = $dept->value;
            }
        }
        return $targetDepartments;
    }

    /**
     * Apply jurisdiction-based query scoping for a user.
     * Centralized here to ensure consistency with isUserEligible().
     */
    public function applyVisibilityScope(\Illuminate\Database\Eloquent\Builder $query, User $user, ?array $statuses = null): void
    {
        $query->where(function ($q) use ($user, $statuses) {
            // Seed with false to ensure 'orWhere' criteria are strictly additive
            $q->whereRaw('1 = 0');

            // 1. Purchaser (Oversight: IN_REVIEW, APPROVED, REJECTED for PRs ONLY)
            if ($user->hasRole('purchaser')) {
                $depts = $this->getPurchaserSpecializedDepartments($user);
                $q->orWhere(function ($sub) use ($depts, $statuses) {
                    $targetStatuses = $statuses ?? ['IN_REVIEW', 'APPROVED', 'REJECTED', 'CANCELED'];
                    $sub->whereIn('status', $targetStatuses)
                        ->whereHasMorph('approvable', [\App\Models\PurchaseRequest::class], function ($aq) use ($depts) {
                            // If specialized sub-roles are defined, restrict scope to them. 
                            // Otherwise (Base Purchaser only), allow global PR access.
                            if (!empty($depts)) {
                                $aq->whereIn('to_department', $depts);
                            }
                        });
                });
            }

            // 2. Dept Head / Supervisor (Oversight: Historical APPROVED/REJECTED ONLY)
            if ($user->hasRole('department-head') || $user->hasRole('supervisor')) {
                $eligibleDepts = $this->getEligibleDepartments($user);
                $eligibleDeptIds = \App\Infrastructure\Persistence\Eloquent\Models\Department::whereIn('name', $eligibleDepts)
                    ->pluck('id')
                    ->toArray();

                $q->orWhere(function ($sub) use ($eligibleDepts, $eligibleDeptIds, $statuses) {
                    $targetStatuses = $statuses ?? ['APPROVED', 'REJECTED'];
                    $sub->whereIn('status', $targetStatuses)
                        ->whereHasMorph('approvable', '*', function ($aq, $type) use ($eligibleDepts, $eligibleDeptIds) {
                            if (in_array($type, [\App\Models\PurchaseRequest::class, \App\Models\SuratPerintahKerja::class])) {
                                $aq->whereIn('from_department', $eligibleDepts);
                            } elseif ($type === \App\Domain\Overtime\Models\OvertimeForm::class) {
                                $aq->whereIn('dept_id', $eligibleDeptIds ?: [0]);
                            }
                        });
                });
            }

            // 3. Verificator (Oversight: APPROVED ONLY)
            if ($user->hasRole('verificator')) {
                $targetStatuses = $statuses ?? ['APPROVED'];
                $q->orWhereIn('status', $targetStatuses);
            }

            // 4. General Manager (Oversight: Branch-specific)
            if ($user->hasRole('general-manager')) {
                $userBranch = $user->employee->branch ?? '';
                if ($userBranch) {
                    $q->orWhere(function ($sub) use ($userBranch, $statuses) {
                        $targetStatuses = $statuses ?? ['IN_REVIEW', 'APPROVED', 'REJECTED'];
                        $sub->whereIn('status', $targetStatuses)
                            ->whereHasMorph('approvable', [\App\Models\PurchaseRequest::class, \App\Domain\Overtime\Models\OvertimeForm::class], function ($aq, $type) use ($userBranch) {
                                if ($type === \App\Models\PurchaseRequest::class) {
                                    // Item branch is an Enum (Branch::JAKARTA or Branch::KARAWANG)
                                    $aq->where('branch', strtoupper($userBranch));
                                } else {
                                    // Item branch is a String (may be 'Jakarta' or 'Karawang')
                                    $aq->whereRaw('LOWER(branch) = ?', [strtolower($userBranch)]);
                                }
                            });
                    });
                }
            }

            // 5. Global View-All Permissions (Permission-Based Oversight)
            // This replaces the previous 'early return' in the Visibility Scoper 
            // for non-SuperAdmins, allowing for state-restricted oversight.
            $canGlobalView = $user->can('approval.view-all') || 
                            $user->can('overtime.view-all') || 
                            $user->can('purchase-request.view-all');

            if ($canGlobalView) {
                if ($user->hasRole('director')) {
                    // Directors see everything globally in index views
                    $q->orWhereIn('status', $statuses ?? ['IN_REVIEW', 'APPROVED', 'REJECTED']);
                } else {
                    // Non-Director Global Viewers (e.g. Verificator with view-all perms)
                    // only see finalized results (APPROVED/REJECTED) globally.
                    // Pending records remain restricted to their own jurisdiction above.
                    $q->orWhereIn('status', $statuses ?? ['APPROVED', 'REJECTED']);
                }
            }
        });
    }
}
