<?php

namespace App\Application\PurchaseRequest\Services;

use App\Enums\ToDepartment;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class PurchaseRequestQueryScoper
{
    /**
     * Apply query scoping based on user role and permissions
     */
    /**
     * Apply query scoping based on user role and permissions
     */
    public function scopeForUser(User $user, Builder $query): Builder
    {
        // 1. Super Admin: View All
        if ($user->hasRole('super-admin')) {
            return $query;
        }

        return $query->where(function ($groupedQuery) use ($user) {
            // 2. Multi-Role Scoping (A user sees anything that matches ANY of these criteria)

            // A. Owner: Always sees their own
            $groupedQuery->orWhere('user_id_create', $user->id);

            // B. Historical Approver: Sees anything they signed
            $groupedQuery->orWhereHas('approvalRequest.steps', function ($sq) use ($user) {
                $sq->where('acted_by', $user->id);
            });

            // C. Director: Sees their turn OR any closed PRs (Approved/Rejected)
            if ($user->hasRole('director')) {
                $groupedQuery->orWhere(function ($dq) use ($user) {
                    $dq->whereHas('approvalRequest', function ($aq) {
                        $aq->whereIn('status', ['APPROVED', 'REJECTED']);
                    });
                    $dq->orWhere($this->buildActiveTurnFilter($user));
                });
            }

            // D. Verificator: ONLY sees their turn
            if ($user->hasRole('verificator')) {
                $groupedQuery->orWhere($this->buildActiveTurnFilter($user));
            }

            // E. GM: Sees their turn (scoped by branch)
            if ($user->hasRole('general-manager')) {
                $groupedQuery->orWhere(function ($gq) use ($user) {
                    $gq->where($this->buildActiveTurnFilter($user));
                    
                    $branch = (string)($user->employee->branch ?? '');
                    if ($branch) {
                        $gq->where('branch', strtoupper(trim($branch)));
                    }
                });
            }

            // F. Purchaser: Sees all PRs for their specialized departments (Regardless of turn)
            if ($user->hasRole('purchaser')) {
                $targetDepartments = $this->getPurchaserSpecializedDepartments($user);
                if (! empty($targetDepartments)) {
                    $groupedQuery->orWhereIn('to_department', $targetDepartments);
                }
            }

            // G. Dept Head: Sees their department's originations
            if ($user->hasRole('department-head')) {
                $deptName = $user->department->name ?? '';
                if ($deptName) {
                    $groupedQuery->orWhere('from_department', $deptName);
                }
            }
        });
    }

    /**
     * Build an OR condition that catches if it is currently the user's turn
     * (either by specific User ID or by a Role they possess).
     */
    private function buildActiveTurnFilter(User $user): \Closure
    {
        return function ($q) use ($user) {
            $q->whereHas('approvalRequest', function ($aq) use ($user) {
                $aq->where('status', 'IN_REVIEW')
                   ->whereHas('steps', function ($sq) use ($user) {
                       $sq->whereColumn('sequence', 'approval_requests.current_step')
                          ->where(function ($match) use ($user) {
                              $match->where(function ($uMatch) use ($user) {
                                  $uMatch->where('approver_type', 'user')
                                         ->where('approver_id', $user->id);
                              })
                              ->orWhere(function ($rMatch) use ($user) {
                                  $roleIds = $user->roles->pluck('id')->toArray();
                                  $rMatch->where('approver_type', 'role')
                                         ->whereIn('approver_id', $roleIds);
                              });
                          });
                   });
            });
        };
    }

    private function getPurchaserSpecializedDepartments(User $user): array
    {
        $targetDepartments = [];
        $roles = $user->getRoleNames();

        foreach ($roles as $role) {
            if (str_starts_with($role, 'purchaser-')) {
                $slug = str_replace('purchaser-', '', $role);
                $dept = \App\Enums\ToDepartment::tryFromSlug($slug);
                if ($dept) {
                    $targetDepartments[] = $dept->value;
                }
            }
        }

        return $targetDepartments;
    }

    private function scopeForOwner(User $user, Builder $query): Builder
    {
        return $query->where('user_id_create', $user->id);
    }
}
