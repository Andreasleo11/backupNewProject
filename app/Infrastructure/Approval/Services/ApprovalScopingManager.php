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
     * Used by the Notification Engine and Action Authorization.
     */
    public function isUserEligible(User $user, string $roleSlug, Approvable $approvable): bool
    {
        // 1. Department-Scoped Roles (Origination Match)
        $deptScopedRoles = ['department-head', 'supervisor', 'verificator', 'purchasing-manager'];
        if (in_array($roleSlug, $deptScopedRoles)) {
            $formDept = $approvable->getApprovableDepartmentName();
            if (! $formDept) {
                return false;
            }

            $eligibleDepts = $this->getEligibleDepartments($user);

            return in_array(
                strtoupper(trim($formDept)),
                array_map('strtoupper', array_map('trim', $eligibleDepts))
            );
        }

        // 2. Branch-Scoped Roles (Branch Match)
        if ($roleSlug === 'general-manager') {
            $formBranch = $approvable->getApprovableBranchValue();
            $userBranch = $user->employee->branch ?? '';

            if (! $formBranch || ! $userBranch) {
                return false;
            }

            return strtolower(trim((string) $userBranch)) === strtolower(trim((string) $formBranch));
        }

        // 3. Specialized PR Purchaser (Category Match or Global Fallback)
        if ($roleSlug === 'purchaser' && $approvable instanceof \App\Models\PurchaseRequest) {
            if (! $approvable->to_department) {
                return false;
            }

            // check for specialized sub-roles (e.g. purchaser-moulding)
            $targetRole = 'purchaser-'.Str::slug($approvable->to_department->label());
            $hasSpecializedRole = $user->hasRole($targetRole) || $user->hasRole(str_replace('purchaser-', '', $targetRole));

            if ($hasSpecializedRole) {
                return true;
            }

            // Global fallback: Only if they have 'purchaser' but NO other specialized sub-roles
            $specializedDepts = $this->getPurchaserSpecializedDepartments($user);

            return empty($specializedDepts) && $user->hasRole('purchaser');
        }

        // 4. Global Oversight Roles (Director, etc.)
        return true;
    }

    /**
     * Determine if a user wants to receive a notification for a specific module
     * based on their global and module-specific preferences.
     */
    public function wantsNotification(User $user, string $moduleClass, string $requestedMode = 'immediate'): bool
    {
        // 1. Check for module-specific override in user preferences
        $preferences = $user->notification_preferences ?? [];
        $mode = $preferences[$moduleClass] ?? null;

        // 2. Fallback to global notification mode
        if (empty($mode)) {
            $mode = $user->email_notification_mode ?? 'immediate';
        }

        // 3. Check for global opt-out
        if ($mode === 'none') {
            return false;
        }

        // 4. Match the requested notification delivery mode
        if ($requestedMode === 'immediate') {
            return in_array($mode, ['immediate', 'both']);
        }

        if ($requestedMode === 'daily_summary') {
            return in_array($mode, ['daily_summary', 'both']);
        }

        return false;
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
     * 
     * Refactored: Dispatch to module-specific scoping methods for better context isolation.
     */
    public function applyVisibilityScope(\Illuminate\Database\Eloquent\Builder $query, User $user, ?array $statuses = null): void
    {
        $query->where(function ($q) use ($user, $statuses) {
            // Seed with false to ensure 'orWhere' criteria are strictly additive
            $q->whereRaw('1 = 0');

            // 1. Purchase Request Module Scoping
            $this->applyPurchaseRequestScope($q, $user, $statuses);

            // 2. Overtime Module Scoping
            $this->applyOvertimeScope($q, $user, $statuses);

            // 3. Global Oversight Permissions (Directors/Admins)
            $this->applyGlobalOversightScope($q, $user, $statuses);
        });
    }

    /**
     * Context: Purchase Request (PR) Visibility Rules
     */
    private function applyPurchaseRequestScope(\Illuminate\Database\Eloquent\Builder $query, User $user, ?array $statuses = null): void
    {
        $query->orWhere(function ($q) use ($user, $statuses) {
            $q->whereRaw('1 = 0'); // Isolated sub-group inner seed

            // A. Purchaser Role (Category Isolation)
            // Skip role-scoping if they already have global module access
            $hasWidePrAccess = $user->can('pr.admin') || $user->can('pr.view-all') || $user->can('system.admin');
            
            if ($user->can('pr.view') && $user->hasRole('purchaser') && !$hasWidePrAccess) {
                $depts = $this->getPurchaserSpecializedDepartments($user);
                $targetStatuses = $statuses ?? ['IN_REVIEW', 'APPROVED', 'REJECTED', 'CANCELED'];
                
                $q->orWhere(function ($sub) use ($depts, $targetStatuses) {
                    $sub->whereIn('status', $targetStatuses)
                        ->whereHasMorph('approvable', [\App\Models\PurchaseRequest::class], function ($aq) use ($depts) {
                            if (!empty($depts)) {
                                $aq->whereIn('to_department', $depts);
                            }
                        });
                });
            }

            // B. Dept Head / Supervisor (Internal PR Isolation)
            if ($user->can('pr.view') && ($user->hasRole('department-head') || $user->hasRole('supervisor')) && !$hasWidePrAccess) {
                $eligibleDepts = $this->getEligibleDepartments($user);
                $targetStatuses = $statuses ?? ['APPROVED', 'REJECTED'];

                $q->orWhere(function ($sub) use ($eligibleDepts, $targetStatuses) {
                    $sub->whereIn('status', $targetStatuses)
                        ->whereHasMorph('approvable', [\App\Models\PurchaseRequest::class], function ($aq) use ($eligibleDepts) {
                            $lowerDepts = array_map('strtolower', array_map('trim', $eligibleDepts));
                            $aq->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(from_department)'), $lowerDepts);
                        });
                });
            }

            // C. General Manager (Branch Isolation)
            if ($user->can('pr.view') && $user->hasRole('general-manager') && !$hasWidePrAccess) {
                $userBranch = $user->employee?->branch ?? '';
                if ($userBranch) {
                    $targetStatuses = $statuses ?? ['IN_REVIEW', 'APPROVED', 'REJECTED'];
                    $q->orWhere(function ($sub) use ($userBranch, $targetStatuses) {
                        $sub->whereIn('status', $targetStatuses)
                            ->whereHasMorph('approvable', [\App\Models\PurchaseRequest::class], function ($aq) use ($userBranch) {
                                $aq->whereRaw('LOWER(branch) = ?', [strtolower($userBranch)]);
                            });
                    });
                }
            }
        });
    }

    /**
     * Context: Overtime Visibility Rules
     */
    private function applyOvertimeScope(\Illuminate\Database\Eloquent\Builder $query, User $user, ?array $statuses = null): void
    {
        $query->orWhere(function ($q) use ($user, $statuses) {
            $q->whereRaw('1 = 0');

            $hasWideOvertimeAccess = $user->can('overtime.view-all') || $user->can('system.admin');

            // A. Dept Head / Supervisor (Department ID Isolation)
            if ($user->can('overtime.view') && ($user->hasRole('department-head') || $user->hasRole('supervisor')) && !$hasWideOvertimeAccess) {
                $eligibleDepts = $this->getEligibleDepartments($user);
                $eligibleDeptIds = \App\Infrastructure\Persistence\Eloquent\Models\Department::whereIn('name', $eligibleDepts)
                    ->pluck('id')
                    ->toArray();
                
                $targetStatuses = $statuses ?? ['APPROVED', 'REJECTED'];
                $q->orWhere(function ($sub) use ($eligibleDeptIds, $targetStatuses) {
                    $sub->whereIn('status', $targetStatuses)
                        ->whereHasMorph('approvable', [\App\Domain\Overtime\Models\OvertimeForm::class], function ($aq) use ($eligibleDeptIds) {
                            $aq->whereIn('dept_id', $eligibleDeptIds ?: [0]);
                        });
                });
            }

            // B. General Manager (Branch Name Matching)
            if ($user->can('overtime.view') && $user->hasRole('general-manager') && !$hasWideOvertimeAccess) {
                $userBranch = $user->employee?->branch ?? '';
                if ($userBranch) {
                    $targetStatuses = $statuses ?? ['IN_REVIEW', 'APPROVED', 'REJECTED'];
                    $q->orWhere(function ($sub) use ($userBranch, $targetStatuses) {
                        $sub->whereIn('status', $targetStatuses)
                            ->whereHasMorph('approvable', [\App\Domain\Overtime\Models\OvertimeForm::class], function ($aq) use ($userBranch) {
                                $aq->whereRaw('LOWER(branch) = ?', [strtolower($userBranch)]);
                            });
                    });
                }
            }
        });
    }

    /**
     * Context: Global Oversight (Directors, Verificators, Specialized Admins)
     */
    private function applyGlobalOversightScope(\Illuminate\Database\Eloquent\Builder $query, User $user, ?array $statuses = null): void
    {
        $canGlobalView = $user->can('approval.view-all') || 
                         $user->can('overtime.view-all') || 
                         $user->can('pr.view-all');

        if ($canGlobalView || $user->hasRole('verificator')) {
            $query->orWhere(function ($q) use ($user, $statuses) {
                // Director sees everything in index views
                if ($user->hasRole('director')) {
                    $q->orWhereIn('status', $statuses ?? ['IN_REVIEW', 'APPROVED', 'REJECTED']);
                } 
                // Verificator and other non-director global viewers only see finalized results
                else {
                    $q->orWhereIn('status', $statuses ?? ['APPROVED', 'REJECTED']);
                }
            });
        }
    }
    
    /**
     * Determine if a user has jurisdictional authority over a specific approvable entity.
     * This is the "Single Source of Truth" for detail-view access and action-level oversight.
     *
     * Rules:
     * - Admins/Directors: Global match.
     * - Dept Heads: Match by User Departments (including linked ones).
     * - GMs: Match by Branch.
     * - Purchasers: Match by Specialized Category (PR-specific).
     */
    public function hasJurisdiction(User $user, Approvable $approvable): bool
    {
        // 1. Global Bypass (Admin)
        if ($user->can('system.admin')) {
            return true;
        }

        // 2. Module Identity & Modular Permissions
        $isPr = ($approvable instanceof \App\Models\PurchaseRequest);
        $isOvertime = ($approvable instanceof \App\Domain\Overtime\Models\OvertimeForm);

        if ($isPr) {
            // PR Admin bypass
            if ($user->can('pr.admin') || $user->can('pr.view-all')) {
                return true;
            }
            // Base PR view permission required for any oversight
            if (! $user->can('pr.view')) {
                return false;
            }
        }

        if ($isOvertime) {
            // Overtime Admin bypass
            if ($user->can('overtime.view-all')) {
                return true;
            }
            // Base Overtime view permission required for any oversight
            if (! $user->can('overtime.view')) {
                return false;
            }
        }

        // 3. Role-Based Jurisdiction Logic
        $userRoles = $user->getRoleNames();

        // A. General Manager (Branch Isolation)
        if ($userRoles->contains('general-manager')) {
            $formBranch = $approvable->getApprovableBranchValue();
            $userBranch = $user->employee->branch ?? '';
            if ($formBranch && $userBranch) {
                if (strtolower(trim((string) $formBranch)) === strtolower(trim((string) $userBranch))) {
                    return true;
                }
            }
        }

        // B. Dept Head / Supervisor / Verificator (Department Isolation)
        $oversightRoles = ['department-head', 'supervisor', 'verificator', 'purchasing-manager'];
        if ($userRoles->intersect($oversightRoles)->isNotEmpty()) {
            $formDept = $approvable->getApprovableDepartmentName();
            if ($formDept) {
                $eligibleDepts = $this->getEligibleDepartments($user);
                $match = in_array(
                    strtoupper(trim($formDept)),
                    array_map('strtoupper', array_map('trim', $eligibleDepts))
                );
                if ($match) {
                    return true;
                }
            }
        }

        // C. Specialized Module Hooks (Purchaser Categories)
        if ($isPr && $userRoles->contains('purchaser')) {
            $targetDepts = $this->getPurchaserSpecializedDepartments($user);

            // Global Fallback: Base 'purchaser' role grants global access if no sub-roles are present
            if (empty($targetDepts)) {
                return true;
            }

            // Specialized Restriction: Must match one of the sub-roles if they exist
            /** @var \App\Models\PurchaseRequest $approvable */
            if ($approvable->to_department && in_array($approvable->to_department->value, $targetDepts)) {
                return true;
            }
        }

        // D. Director (Top-Level Oversight)
        if ($userRoles->contains('director')) {
            return true;
        }

        return false;
    }
}
