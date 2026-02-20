<?php

namespace App\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\PurchaseRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('pr.view-all') || $user->can('pr.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PurchaseRequest $pr): bool
    {
        if ($user->can('pr.view-all')) {
            return true;
        }

        if ($user->can('pr.view')) {
            // Can view if created by self
            if ($user->id === $pr->user_id_create) {
                return true;
            }
            // Can view if in same department
            if ($user->department_id === $pr->from_department_id) {
                // Additional check: usually view-dept implies they should see everything in dept,
                // but sometimes standard requesters only see their own.
                // For now, let's assume 'pr.view' with department check allows department visibility
                // OR we can make a stricter 'pr.view-dept' if needed.
                // Based on previous rules: Requesters usually only see their own?
                // Let's assume 'pr.view' is basic access.
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('pr.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseRequest $pr): bool
    {
        if ($user->can('pr.edit')) {
            // Standard edit rule: DRAFT, RETURNED, or REJECTED
            // We check workflow_status for modern flow, fall back to status for legacy if needed
            $allowed = ['DRAFT', 'RETURNED', 'REJECTED'];
            $status = $pr->workflow_status ?? 'DRAFT';

            if (in_array($status, $allowed)) {
                // Must be creator
                return $user->id === $pr->user_id_create;
            }

            // Legacy fallback (status 1 = draft)
            if ($pr->status === 1) {
                return $user->id === $pr->user_id_create;
            }
        }

        // Purchasers might edit at later stages
        if ($user->hasRole('pr-purchaser') && in_array($pr->status, [1, 6])) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseRequest $pr): bool
    {
        if ($user->can('pr.delete')) {
            // Only draft
            return $pr->status === 1 && $user->id === $pr->user_id_create;
        }

        return false;
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(User $user, PurchaseRequest $pr): bool
    {
        // Cancel allowed if draft or just submitted?
        // Usually cancel is allowed if user owns it and it's not fully processed.
        if ($user->can('pr.cancel')) {
            return $user->id === $pr->user_id_create || $user->hasRole('pr-purchaser');
        }

        return false;
    }

    /**
     * Determine if user can approve the PR (Workflow engine check).
     */
    public function approve(User $user, PurchaseRequest $pr): bool
    {
        // This is usually handled by the workflow engine service,
        // but we can wrap the permission check here.
        return $user->can('approval.approve');
    }

    // Add other actions as needed
    public function uploadFiles(User $user, PurchaseRequest $pr): bool
    {
        return $user->can('pr.upload-files');
    }

    /**
     * Determine if user can perform batch approval/rejection.
     */
    public function batchApprove(User $user): bool
    {
        return $user->can('pr.batch-approve');
    }
}
