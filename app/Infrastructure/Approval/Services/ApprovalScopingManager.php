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
     * Centralized mapping for linked departments to ensure modularity.
     */
    private const DEPARTMENT_LINKS = [
        'LOGISTIC' => ['STORE'],
        'QC'       => ['QA'],
    ];

    /**
     * Get all departments (primary + linked) that a user is responsible for.
     */
    public function getEligibleDepartments(User $user): array
    {
        $baseDeptName = $user->employee->department->name ?? '';
        $linkedDepts = self::DEPARTMENT_LINKS[$baseDeptName] ?? [];
        
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
    public function applyVisibilityScope(\Illuminate\Database\Eloquent\Builder $query, User $user): void
    {
        $query->where(function ($q) use ($user) {
            // Seed with false to ensure 'orWhere' criteria are strictly additive
            $q->whereRaw('1 = 0');

            // 1. Purchaser (Target Dept Match for Purchase Requests ONLY)
            if ($user->hasRole('purchaser')) {
                $depts = $this->getPurchaserSpecializedDepartments($user);
                if (!empty($depts)) {
                    $q->orWhere(function ($sub) use ($depts) {
                        $sub->where('approvable_type', \App\Models\PurchaseRequest::class)
                            ->whereHasMorph('approvable', [\App\Models\PurchaseRequest::class], function ($aq) use ($depts) {
                                $aq->whereIn('to_department', $depts);
                            });
                    });
                }
            }

            // 2. General Manager (Branch Match for All Modules)
            if ($user->hasRole('general-manager')) {
                $branch = strtoupper(trim((string)($user->employee->branch ?? '')));
                if ($branch) {
                    $q->orWhereHasMorph('approvable', '*', function ($aq) use ($branch) {
                        $aq->where('branch', $branch);
                    });
                }
            }

            // 3. Dept Head / Supervisor (Origination Match with Linked Departments)
            if ($user->hasRole('department-head') || $user->hasRole('supervisor')) {
                $eligibleDepts = $this->getEligibleDepartments($user);
                
                // OvertimeForm uses FK(id) instead of string name, so we must resolve IDs
                $eligibleDeptIds = \App\Infrastructure\Persistence\Eloquent\Models\Department::whereIn('name', $eligibleDepts)
                    ->pluck('id')
                    ->toArray();

                $q->orWhereHasMorph('approvable', '*', function ($aq, $type) use ($eligibleDepts, $eligibleDeptIds) {
                    $aq->where(function ($sq) use ($type, $eligibleDepts, $eligibleDeptIds) {
                        if (in_array($type, [
                            \App\Models\PurchaseRequest::class,
                            \App\Models\SuratPerintahKerja::class,
                        ])) {
                            $sq->whereIn('from_department', $eligibleDepts);
                        } elseif ($type === \App\Domain\Overtime\Models\OvertimeForm::class) {
                            if (!empty($eligibleDeptIds)) {
                                $sq->whereIn('dept_id', $eligibleDeptIds);
                            } else {
                                $sq->whereRaw('1 = 0');
                            }
                        } else {
                            $sq->whereRaw('1 = 0');
                        }
                    });
                });
            }

            // 4. Global Oversight Roles (Director, Verificator)
            if ($user->hasRole('director') || $user->hasRole('verificator')) {
                $q->orWhereRaw('1 = 1');
            }
        });
    }
}
