<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ApprovalFlowStep;
use App\Models\HeaderFormOvertime;
use App\Infrastructure\Persistence\Eloquent\Models\User;

/**
 * RBAC policy for the Overtime Form feature.
 *
 * All authorization checks in Livewire components, controllers,
 * and the approval service should delegate here.
 */
class OvertimePolicy
{
    /**
     * Any authenticated user can view the overtime index.
     * Visibility is further scoped by scopeByRole() in the Livewire component.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * MANAGEMENT department members cannot create overtime forms.
     * Everyone else can.
     */
    public function create(User $user): bool
    {
        return $user->department?->name !== 'MANAGEMENT';
    }

    /**
     * Only the form creator or super-admin can delete a form.
     */
    public function delete(User $user, HeaderFormOvertime $form): bool
    {
        return $user->id === $form->user_id || $user->hasRole('super-admin');
    }

    /**
     * A form can be edited by its creator (or super-admin) while it is
     * still in a waiting-creator or waiting-dept-head status.
     */
    public function update(User $user, HeaderFormOvertime $form): bool
    {
        $editableStatuses = ['waiting-creator', 'waiting-dept-head'];

        return in_array($form->status, $editableStatuses, true)
            && ($user->id === $form->user_id || $user->hasRole('super-admin'));
    }

    /**
     * A user can sign (approve) a step if they hold the role_slug
     * defined on that step, or they are super-admin.
     */
    public function sign(User $user, HeaderFormOvertime $form, ApprovalFlowStep $step): bool
    {
        return $user->hasRole($step->role_slug) || $user->hasRole('super-admin');
    }

    /**
     * A user can reject the form if they can sign the current pending step.
     */
    public function reject(User $user, HeaderFormOvertime $form): bool
    {
        $step = $form->currentStep();

        if (! $step) {
            return false; // Nothing to reject (already terminal).
        }

        return $this->sign($user, $form, $step);
    }

    /**
     * Exports are limited to VERIFICATOR and above.
     */
    public function export(User $user): bool
    {
        return $user->hasAnyRole(['VERIFICATOR', 'DIRECTOR', 'GM', 'super-admin']);
    }

    /**
     * JPayroll push is limited to super-admin and VERIFICATOR.
     */
    public function pushToPayroll(User $user): bool
    {
        return $user->hasAnyRole(['VERIFICATOR', 'super-admin']);
    }
}
