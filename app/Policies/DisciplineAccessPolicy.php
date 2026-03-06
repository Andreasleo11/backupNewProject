<?php

namespace App\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DisciplineAccessPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view the department's evaluation (Dept Head).
     */
    public function viewDepartment(User $user): bool
    {
        return $user->can('evaluation.view-department')
            || $user->is_head === 1
            || $this->isSuperAccessUser($user);
    }

    /**
     * Determine if the user can view ALL company evaluations (GM, HRD, Super Admin).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('evaluation.view-any')
            || $user->is_gm
            || $this->isSuperAccessUser($user)
            || $this->isHrdApprover($user);
    }

    /**
     * Determine if the user can grade an evaluation.
     */
    public function grade(User $user): bool
    {
        return $user->can('evaluation.grade')
            || $user->is_head === 1;
    }

    /**
     * Determine if the user can approve department evaluations.
     */
    public function approveDepartment(User $user): bool
    {
        return $user->can('evaluation.approve-department')
            || $user->is_head === 1;
    }

    /**
     * Determine if the user has final approval authority (HRD, GM).
     */
    public function approveFinal(User $user): bool
    {
        return $user->can('evaluation.approve-final')
            || $user->is_gm
            || $this->isHrdApprover($user)
            || $this->isSuperAccessUser($user);
    }

    /**
     * Determine if the user can see the Regular employee tab.
     *
     * Logic:
     *  - Super access / HRD / GM always see all tabs.
     *  - If the user has ANY explicit evaluation.view-* permission assigned,
     *    only the tabs they are explicitly granted are shown (fine-grained mode).
     *  - Otherwise, any user who can access the evaluation page sees all three tabs.
     */
    public function viewRegular(User $user): bool
    {
        if ($this->isSuperAccessUser($user) || $user->is_gm || $this->isHrdApprover($user)) {
            return true;
        }

        if ($this->hasExplicitTabPermissions($user)) {
            return $user->can('evaluation.view-regular');
        }

        return $this->viewDepartment($user) || $this->viewAny($user);
    }

    /**
     * Determine if the user can see the Yayasan employee tab.
     */
    public function viewYayasan(User $user): bool
    {
        if ($this->isSuperAccessUser($user) || $user->is_gm || $this->isHrdApprover($user)) {
            return true;
        }

        if ($this->hasExplicitTabPermissions($user)) {
            return $user->can('evaluation.view-yayasan');
        }

        return $this->viewDepartment($user) || $this->viewAny($user);
    }

    /**
     * Determine if the user can see the Magang employee tab.
     */
    public function viewMagang(User $user): bool
    {
        if ($this->isSuperAccessUser($user) || $user->is_gm || $this->isHrdApprover($user)) {
            return true;
        }

        if ($this->hasExplicitTabPermissions($user)) {
            return $user->can('evaluation.view-magang');
        }

        return $this->viewDepartment($user) || $this->viewAny($user);
    }

    // ──────────────────────────────────────────────────────
    // Legacy Config Checks (Preserved as unmigrated fallbacks)
    // ──────────────────────────────────────────────────────

    private function isHrdApprover(User $user): bool
    {
        return in_array($user->email, config('discipline.hrd_approvers', []), true);
    }

    private function isSuperAccessUser(User $user): bool
    {
        return in_array($user->email, config('discipline.super_access_emails', []), true)
            || in_array($user->id, config('discipline.special_access_ids', []), true)
            || $user->hasRole('super-admin');
    }

    /**
     * True if the user has ANY explicit tab-scoped permission assigned.
     * When true, they are in "restricted mode" — only explicitly granted tabs are shown.
     */
    private function hasExplicitTabPermissions(User $user): bool
    {
        return $user->can('evaluation.view-regular')
            || $user->can('evaluation.view-yayasan')
            || $user->can('evaluation.view-magang');
    }
}

