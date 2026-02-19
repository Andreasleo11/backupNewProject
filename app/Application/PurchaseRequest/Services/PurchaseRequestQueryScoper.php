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

        // 2. View All Permission (Director, GM, etc if configured)
        // Check specific permission first?
        // Actually, user said Purchasers have 'view-all' but need filtering.
        // So we must check specific roles/logic first before generic 'view-all'.

        // 3. Purchaser: Filter by Target Department
        if ($user->hasRole('pr-purchaser')) {
            return $this->scopeForPurchaser($user, $query);
        }

        // 4. View All (Global access for Director/GM who are NOT purchasers)
        if ($user->can('pr.view-all')) {
            return $query;
        }

        // 5. Dept Head: View Department's Requests
        if ($user->hasRole('pr-dept-head')) {
            return $this->scopeForHead($user, $query);
        }

        // 6. Regular User: View Own
        return $this->scopeForOwner($user, $query);
    }

    private function scopeForPurchaser(User $user, Builder $query): Builder
    {
        $deptName = strtoupper($user->department->name ?? '');

        // Map User Department to PR 'to_department' Enum/Value
        // PR `to_department` columns store: Purchasing, Maintenance, Computer, Personnel.
        // Or sometimes numeric/enum. Based on ToDepartment enum:
        // Case handling based on common enum values
        $target = match ($deptName) {
            'PURCHASING' => \App\Enums\ToDepartment::PURCHASING->value,
            'MAINTENANCE' => \App\Enums\ToDepartment::MAINTENANCE->value,
            'COMPUTER', 'IT' => \App\Enums\ToDepartment::COMPUTER->value,
            'PERSONNEL', 'HRD', 'PERSONALIA' => \App\Enums\ToDepartment::PERSONALIA->value,
            default => null
        };

        if ($target) {
            return $query->where('to_department', $target);
        }

        // Fallback: If no matching target dept, maybe they can view all?
        // Or view nothing?
        // User said: "each to department is handled by different user"
        // If we can't map it, safer to show nothing or just their own.
        // Let's default to showing their own creation just in case
        return $query->where('user_id_create', $user->id);
    }

    private function scopeForHead(User $user, Builder $query): Builder
    {
        // View requests originating FROM their department
        if ($user->department_id) {
            // If we have ID relationship
            return $query->where('from_department_id', $user->department_id);
        }

        // Fallback to name match if ID not set (legacy)
        $deptName = $user->department->name ?? '';

        return $query->where('from_department', $deptName);
    }

    private function scopeForOwner(User $user, Builder $query): Builder
    {
        return $query->where('user_id_create', $user->id);
    }
}
