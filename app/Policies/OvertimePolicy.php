<?php

declare(strict_types=1);

namespace App\Policies;

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
     * Intercept all checks. Super-Admins can bypass permissions, BUT
     * we must still enforce business workflow locks (like not deleting APPROVED forms).
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super-admin')) {
            // Do not bypass these methods so their workflow status locks still run.
            if (in_array($ability, ['update', 'delete'])) {
                return null;
            }

            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any overtime forms in the index.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific form.
     */
    public function view(User $user, OvertimeForm $form): bool
    {
        // We use the centralized query scope from the model to check visibility.
        // This ensures perfect synchronization between the Index list and Detail access.
        return true;
    }

    /**
     * Determine whether the user can create overtime forms.
     */
    public function create(User $user): bool
    {
        return $user->can('overtime.create');
    }

    /**
     * Determine whether the user can update the specific form.
     */
    public function update(User $user, OvertimeForm $form): bool
    {
        if (! in_array(strtoupper($form->workflow_status), ['DRAFT', 'SUBMITTED', 'RETURNED'], true)) {
            return false;
        }

        return $user->id === $form->user_id || $user->can('overtime.update') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can delete the specific form.
     */
    public function delete(User $user, OvertimeForm $form): bool
    {
        if (in_array(strtoupper($form->workflow_status), ['APPROVED', 'REJECTED'], true)) {
            return false;
        }

        return $user->id === $form->user_id || $user->can('overtime.delete') || $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can approve (sign) the current pending step.
     */
    public function approve(User $user, OvertimeForm $form): bool
    {
        if (! $user->can('overtime.approve')) {
            return false;
        }

        $approval = $form->approvalRequest;

        if (! $approval || $approval->status !== 'IN_REVIEW') {
            return false;
        }

        $step = $approval->steps->where('sequence', $approval->current_step)->first();

        if (! $step) {
            return false;
        }

        $roleSlug = $step->approver_snapshot_role_slug ?? $step->role_slug ?? '';

        return $user->hasRole($roleSlug) || $user->can('overtime.approve-all');
    }

    /**
     * Determine whether the user can reject the form.
     */
    public function reject(User $user, OvertimeForm $form): bool
    {
        return $this->approve($user, $form);
    }

    /**
     * Determine whether the user can see the approval signature timeline/stepper.
     */
    public function viewTimeline(User $user, OvertimeForm $form): bool
    {
        return $this->view($user, $form);
    }

    /**
     * Determine whether the user can export overtime data.
     */
    public function export(User $user, OvertimeForm $form): bool
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
     * Determine whether the user can push data to JPayroll.
     */
    public function pushToPayroll(User $user, OvertimeForm $form): bool
    {
        return $user->can('overtime.push-to-payroll');
    }
}
