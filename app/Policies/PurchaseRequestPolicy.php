<?php

namespace App\Policies;

use App\Domain\PurchaseRequest\Services\PurchaseRequestSecurityService;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\PurchaseRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseRequestPolicy
{
    use HandlesAuthorization;

    public function __construct(
        private readonly PurchaseRequestSecurityService $security,
    ) {}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('pr.view-all') || $user->can('pr.view') || $user->can('pr.admin');
    }

    /**
     * Determine whether the user can view the model (CRUD-level).
     */
    public function view(User $user, PurchaseRequest $pr): bool
    {
        if ($user->can('pr.admin') || $user->can('pr.view-all')) {
            return true;
        }

        if ($user->can('pr.view')) {
            // Can view if created by self
            if ($user->id === $pr->user_id_create) {
                return true;
            }
            // Can view if in same department
            if ($user->department_id === $pr->from_department_id) {
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
        return $user->can('pr.create') || $user->can('pr.admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PurchaseRequest $pr): bool
    {
        // 1. Basic Permission Check
        if (!$user->can('pr.edit') && !$user->can('pr.admin')) {
            return false;
        }

        $status = $pr->workflow_status ?? 'DRAFT';

        // 2. Creator Logic: Creator can edit in specific states
        if ($user->id === (int) $pr->user_id_create) {
            $allowedForCreator = ['DRAFT', 'RETURNED', 'REJECTED'];
            if (in_array($status, $allowedForCreator)) {
                return true;
            }
        }

        // 3. Sensitive Oversight Logic (e.g., Purchasers/GMs)
        // Can edit in most states (including IN_REVIEW and APPROVED)
        if ($user->can('pr.view-sensitive') || $user->can('pr.admin')) {
            $allowedForOversight = ['DRAFT', 'RETURNED', 'REJECTED', 'IN_REVIEW', 'APPROVED'];
            if (in_array($status, $allowedForOversight)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseRequest $pr): bool
    {
        if ($user->can('pr.delete') || $user->can('pr.admin')) {
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
        if ($user->can('pr.cancel') || $user->can('pr.admin')) {
            return $user->id === (int) $pr->user_id_create || $user->can('pr.view-sensitive');
        }

        return false;
    }

    /**
     * Determine if user can approve the PR (Security bridge to workflow).
     */
    public function approve(User $user, PurchaseRequest $pr): bool
    {
        return $user->can('pr.approve') || $user->can('pr.admin');
    }

    /**
     * Determine if the user is eligible for auto-approval.
     * Delegates to the Domain Service (DDD).
     */
    public function autoApprove(User $user, PurchaseRequest $pr): bool
    {
        return $user->can('pr.auto-approve') 
               || $this->security->canAutoApprove($pr, $user)
               || $user->can('pr.admin');
    }

    /**
     * Determine if user can upload files.
     */
    public function uploadFiles(User $user, PurchaseRequest $pr): bool
    {
        return $user->can('pr.upload-files') || $user->can('pr.admin');
    }

    /**
     * Determine if user can perform batch approval/rejection.
     */
    public function batchApprove(User $user): bool
    {
        return $user->can('pr.batch-approve') || $user->can('pr.admin');
    }
}
