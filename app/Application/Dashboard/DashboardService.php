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
    public function getPendingApprovals(): Collection
    {
        $user = Auth::user();
        if (!$user) return new Collection();

        return ApprovalStep::with(['request.approvable', 'request.rule'])
            ->where('status', 'pending')
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
            ->latest()
            ->take(5)
            ->get();
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
