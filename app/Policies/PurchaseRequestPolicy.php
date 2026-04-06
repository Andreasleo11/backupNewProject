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
     * Global Admin Bypass
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->can('system.admin') || $user->can('pr.admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models (Index view).
     */
    public function viewAny(User $user): bool
    {
        return $user->can('pr.view');
    }

    /**
     * Determine whether the user can view a specific model.
     */
    public function view(User $user, PurchaseRequest $pr): bool
    {
        if (!$user->can('pr.view')) {
            return false;
        }

        // View logic: Creator OR same department
        return $user->id === (int) $pr->user_id_create || 
               $user->department_id === $pr->from_department_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('pr.create');
    }

    /**
     * Determine whether the user can update the model (Data Edit).
     */
    public function update(User $user, PurchaseRequest $pr): bool
    {
        if (!$user->can('pr.edit')) {
            return false;
        }

        return $this->security->canUpdate($pr, $user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseRequest $pr): bool
    {
        if (!$user->can('pr.delete')) {
            return false;
        }

        // Only creators can delete, and only in DRAFT mode
        return $user->id === (int) $pr->user_id_create && 
               strtoupper($pr->workflow_status ?? 'DRAFT') === 'DRAFT';
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(User $user, PurchaseRequest $pr): bool
    {
        if (!$user->can('pr.cancel')) {
            return false;
        }

        return $this->security->canCancel($pr, $user);
    }

    /**
     * Determine if user can approve the PR (Security bridge to workflow).
     */
    public function approve(User $user, PurchaseRequest $pr): bool
    {
        return $user->can('pr.approve');
    }

    /**
     * Determine if the user is eligible for auto-approval.
     */
    public function autoApprove(User $user, PurchaseRequest $pr): bool
    {
        return $user->can('pr.auto-approve') || 
               $this->security->canAutoApprove($pr, $user);
    }

    /**
     * Determine if user can upload files (Merged with Edit permission).
     */
    public function uploadFiles(User $user, PurchaseRequest $pr): bool
    {
        return $this->update($user, $pr);
    }

    /**
     * Determine if user can perform batch actions.
     */
    public function batchApprove(User $user): bool
    {
        return $user->can('pr.batch-approve');
    }
}
