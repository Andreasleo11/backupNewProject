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
            if ($user->hasRole('purchaser')) {
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
            if ($user->hasRole('department-head') || $user->hasRole('supervisor')) {
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
            if ($user->hasRole('general-manager')) {
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

            // A. Dept Head / Supervisor (Department ID Isolation)
            if ($user->hasRole('department-head') || $user->hasRole('supervisor')) {
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
            if ($user->hasRole('general-manager')) {
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
                         $user->can('purchase-request.view-all');

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
}
