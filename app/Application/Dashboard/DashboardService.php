<?php

namespace App\Application\Dashboard;

use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\Models\Activity;

class DashboardService
{
    /**
     * Get pending approvals for the current user.
     */
    public function getPendingApprovals(User $user, ?int $limit = 5): Collection
    {
        $query = $this->getPendingApprovalsQuery($user);

        if ($limit) {
            $query->take($limit);
        }

        return $query->get();
    }

    /**
     * Data source for the paginated approvals page.
     */
    public function getPendingApprovalsQuery(User $user)
    {
        if (! $user) {
            return ApprovalStep::query()->whereRaw('1=0');
        }

        return ApprovalStep::with(['request.approvable', 'request.rule'])
            ->where('status', 'pending')
            ->whereHas('request', function ($q) use ($user) {
                // 1. Strict Status: Only show items in an active 'IN_REVIEW' cycle
                $q->where('status', 'IN_REVIEW')
                  // 2. Local Scope: Respect jurisdiction-based visibility rules (Branch/Dept)
                  // This scope correctly handles specialized purchaser categories as well.
                    ->forUser($user);
            })
            // 3. ONLY return the step if it matches the current active sequence turn
            ->whereExists(function ($query) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('approval_requests')
                    ->whereColumn('approval_requests.id', 'approval_steps.approval_request_id')
                    ->whereColumn('approval_requests.current_step', 'approval_steps.sequence');
            })
            ->where(function ($query) use ($user) {
                // 4. Match the current step either directly to the user or to one of their roles
                $query->where(function ($q) use ($user) {
                    $q->where('approver_type', 'user')
                        ->where('approver_id', $user->id);
                })->orWhere(function ($q) use ($user) {
                    $roleIds = $user->roles->pluck('id')->toArray();
                    $roleNames = $user->getRoleNames()->toArray();

                    $q->where('approver_type', 'role')
                        ->where(function ($q2) use ($roleIds, $roleNames) {
                            if (! empty($roleIds)) {
                                $q2->whereIn('approver_id', $roleIds);
                            }
                            if (! empty($roleNames)) {
                                $q2->orWhereIn('approver_id', $roleNames)
                                    ->orWhereIn('approver_snapshot_role_slug', $roleNames);
                            }
                        });
                });
            })
            ->latest();
    }

    /**
     * Get recent activity logs relevant to the user's department.
     */
    public function getRecentActivities(User $user): Collection
    {
        $query = Activity::with(['causer', 'subject'])->latest()->take(10);

        // Filter by user's employee department
        if ($user->employee && $user->employee->department) {
            $department = $user->employee->department;
            
            $query->whereHasMorph('causer', [$user->getMorphClass()], function ($q) use ($department) {
                $q->whereHas('employee', function ($q2) use ($department) {
                    $q2->where('dept_code', $department->dept_no);
                });
            });
        }

        return $query->get();
    }

    /**
     * Get a summary of counts for different modules.
     */
    public function getKpiSummary(User $user): array
    {
        $cacheKey = "dashboard_kpi_summary_user_{$user->id}";
        
        $kpis = Cache::remember($cacheKey, now()->addMinutes(2), function () use ($user) {
            return [
                'pending_approvals' => $this->getPendingApprovalsQuery($user)->count(),
                'last_fetched_at' => now()->format('Y-m-d H:i:s'),
            ];
        });

        // Ensure last_fetched_at exists for legacy cache entries
        if (!isset($kpis['last_fetched_at'])) {
            $kpis['last_fetched_at'] = now()->format('Y-m-d H:i:s');
        }

        return $kpis;
    }
}
