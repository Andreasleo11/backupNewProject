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
        private readonly \App\Infrastructure\Approval\Services\ApprovalScopingManager $scoper,
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
     * Determine whether the user can view a specific purchase request.
     */
    public function view(User $user, PurchaseRequest $pr): bool
    {
        // 1. Ownership: Always allowed
        if ($this->security->isOwner($pr, $user)) {
            return true;
        }

        // 2. Jurisdictional Oversight (Unified Brain): GMs, Dept Heads, Purchasers, etc.
        // This engine ensures the Policy matches the DataTable (Scoper) perfectly.
        if ($this->scoper->hasJurisdiction($user, $pr)) {
            return true;
        }

        // 3. Workflow Involvement: Anyone who has acted or is currently assigned to this PR.
        // This acts as a fallback for users without the broad 'pr.view' permission.
        if ($pr->approvalRequest) {
            $isApprover = $pr->approvalRequest->steps()
                ->where(function ($q) use ($user) {
                    $q->where('acted_by', $user->id)
                        ->orWhere(function ($aq) use ($user) {
                            $aq->where('approver_type', 'user')->where('approver_id', $user->id);
                        })
                        ->orWhere(function ($aq) use ($user) {
                            $roleNames = $user->getRoleNames()->toArray();
                            $aq->where('approver_type', 'role')->whereIn('approver_id', $roleNames);
                        });
                })
                ->exists();

            if ($isApprover) {
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
     * Determine whether the user can update the model (Data Edit).
     */
    public function update(User $user, PurchaseRequest $pr): bool
    {
        if (! $user->can('pr.edit')) {
            return false;
        }

        return $this->security->canUpdate($pr, $user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PurchaseRequest $pr): bool
    {
        if (! $user->can('pr.delete')) {
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
        if (! $user->can('pr.cancel')) {
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
