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
}
