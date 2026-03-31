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
        if (!$user) return ApprovalStep::query()->whereRaw('1=0');

        return ApprovalStep::with(['request.approvable', 'request.rule'])
            ->where('status', 'pending')
            ->whereHas('request', function ($q) use ($user) {
                // 1. Strict Status: Only show items in an active 'IN_REVIEW' cycle
                $q->where('status', 'IN_REVIEW')
                  // 2. Local Scope: Respect branch/dept visibility logic via centralized scoper
                  ->forUser($user);
            })
            ->where(function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('approver_type', 'user')
                      ->where('approver_id', $user->id);
                })->orWhere(function ($q) use ($user) {
                    $roles = $user->roles->pluck('id')->toArray();
                    $q->where('approver_type', 'role')
                      ->whereIn('approver_id', $roles);
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
        // This would call into other domain services in a full DDD setup
        // For now, keep it simple (KISS)
        return [
            'pending_approvals' => $this->getPendingApprovals()->count(),
            'unread_notifications' => Auth::user()?->unreadNotifications()->count() ?? 0,
            // Add more as modules are integrated
        ];
    }
}
