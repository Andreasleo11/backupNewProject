<?php

namespace App\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DisciplineAccessPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any discipline records (their own dept).
     */
    public function viewAnyDiscipline(User $user): bool
    {
        return $user->is_head === 1 || $this->isSuperAccessUser($user) || $user->hasRole('super-admin');
    }

    /**
     * Determine if the user can view ALL discipline records (cross-dept HR view).
     */
    public function viewAllDiscipline(User $user): bool
    {
        return $this->isSuperAccessUser($user) || $user->hasRole('super-admin');
    }

    /**
     * Determine if the user can view Yayasan or Magang discipline records.
     * Same gate as viewAnyDiscipline — scoping happens in DepartmentEmployeeResolver.
     */
    public function viewYayasanDiscipline(User $user): bool
    {
        return $user->is_head === 1 || $this->isSuperAccessUser($user) || $user->hasRole('super-admin');
    }

    /**
     * Determine if the user is an HRD approver (final Yayasan approval step).
     * Reads from config/discipline.php — no hardcoded emails.
     */
    public function isHrdApprover(User $user): bool
    {
        return in_array($user->email, config('discipline.hrd_approvers', []), true);
    }

    /**
     * Determine if the user has super access (cross-department HR visibility).
     * Reads from config/discipline.php — no hardcoded emails or IDs.
     */
    public function isSuperAccessUser(User $user): bool
    {
        return in_array($user->email, config('discipline.super_access_emails', []), true)
            || in_array($user->id, config('discipline.special_access_ids', []), true);
    }
}

