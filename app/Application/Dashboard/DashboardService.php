<?php

namespace App\Application\Dashboard;

use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class DashboardService
{
    /**
     * Get pending approvals for the current user.
     */
    public function getPendingApprovals(?int $limit = 5): Collection
    {
        $query = $this->getPendingApprovalsQuery();

        if ($limit) {
            $query->take($limit);
        }

        return $query->get();
    }

    /**
     * Data source for the paginated approvals page.
     */
    public function getPendingApprovalsQuery()
    {
        $user = Auth::user();
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
            ->where(function ($query) use ($user) {
                // 3. Match the current step either directly to the user or to one of their roles
                $query->where(function ($q) use ($user) {
                    $q->where('approver_type', 'user')
                      ->where('approver_id', $user->id);
                })->orWhere(function ($q) use ($user) {
                    $roleIds = $user->roles->pluck('id')->toArray();
                    $roleNames = $user->getRoleNames()->toArray();

                    $q->where('approver_type', 'role')
                      ->where(function ($q2) use ($roleIds, $roleNames) {
                          $q2->whereIn('approver_id', $roleIds)
                             ->orWhereIn('approver_id', $roleNames);
                      });
                });
            })
            ->latest();
    }

    /**
     * Get recent activity logs relevant to the user's department.
     */
    public function getRecentActivities(): Collection
    {
        $user = Auth::user();
        $query = Activity::with('causer')->latest()->take(10);

        // Optional: Filter by department if needed
        // if ($user && $user->department_id) { ... }

        return $query->get();
    }

    /**
     * Get a summary of counts for different modules.
     */
    public function getKpiSummary(): array
    {
        $user = Auth::user();

        // Ensure we count the total available pending tasks, not just the limited list.
        return [
            'pending_approvals' => $this->getPendingApprovalsQuery()->count(),
            'unread_notifications' => $user?->unreadNotifications()->count() ?? 0,
        ];
    }
}
