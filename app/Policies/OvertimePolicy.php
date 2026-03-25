<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ApprovalFlowStep;
use App\Domain\Overtime\Models\OvertimeForm;
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
        return $user->can('overtime.view') || $user->can('overtime.view-all');
    }

    /**
     * Determine whether the user can view the specific form.
     */
    public function view(User $user, OvertimeForm $form): bool
    {
        // We use the centralized query scope from the model to check visibility.
        // This ensures perfect synchronization between the Index list and Detail access.
        return OvertimeForm::where('id', $form->id)->byRole($user)->exists();
    }

    /**
     * MANAGEMENT department members cannot create overtime forms.
     * Everyone else can.
     */
    public function create(User $user): bool
    {
        return $user->can('overtime.create') && $user->department?->name !== 'MANAGEMENT';
    }

    /**
     * Only the form creator or super-admin can delete a form.
     */
    public function delete(User $user, OvertimeForm $form): bool
    {
        return $user->id === $form->user_id || $user->can('overtime.delete');
    }

    /**
     * A form can be edited by its creator (or super-admin) while it is
     * still in a waiting-creator or waiting-dept-head status.
     */
    public function update(User $user, OvertimeForm $form): bool
    {
        $editableStatuses = ['waiting-creator', 'waiting-dept-head'];

        return in_array($form->status, $editableStatuses, true)
            && ($user->id === $form->user_id || $user->can('overtime.delete'));
    }

    /**
     * Determine whether the user can approve (sign) the current step.
     */
    public function approve(User $user, OvertimeForm $form): bool
    {
        if (! $user->can('overtime.approve')) {
            return false;
        }

        return $this->sign($user, $form);
    }

    /**
     * Determine whether the user can see the approval signature timeline/stepper.
     */
    public function viewTimeline(User $user, OvertimeForm $form): bool
    {
        return $this->view($user, $form);
    }

    /**
     * A user can sign (approve) a step if they hold the role_slug
     * defined on that step, or they are super-admin.
     */
    public function sign(User $user, OvertimeForm $form, $step = null): bool
    {
        if (! $step) {
            $req = $form->approvalRequest;
            if ($req && $req->status === 'IN_REVIEW') {
                $step = $req->steps->where('sequence', $req->current_step)->first();
            }
        }

        if (! $step) {
            return false;
        }

        $roleSlug = $step->approver_snapshot_role_slug ?? $step->role_slug ?? '';
        return $user->hasRole($roleSlug) || $user->hasRole('super-admin');
    }

    /**
     * A user can reject the form if they can sign the current pending step.
     */
    public function reject(User $user, OvertimeForm $form): bool
    {
        return $this->approve($user, $form);
    }

    /**
     * Exports are limited to VERIFICATOR and above.
     */
    public function export(User $user): bool
    {
        return $user->can('overtime.export');
    }

    /**
     * Determine whether the user can review/manage individual overtime details.
     */
    public function reviewDetail(User $user, OvertimeForm $form): bool
    {
        return $user->can('overtime.review');
    }

    /**
     * JPayroll push is limited to super-admin and VERIFICATOR.
     */
    public function pushToPayroll(User $user): bool
    {
        return $user->can('overtime.push-to-payroll');
    }
}

