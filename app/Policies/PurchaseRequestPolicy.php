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
     * Determine whether the user can view all approved PRs across departments.
     */
    public function viewAllApproved(User $user): bool
    {
        return $user->can('pr.view-all-approved');
    }

    /**
     * Determine whether the user can view a specific purchase request.
     */
    public function view(User $user, PurchaseRequest $pr): bool
    {
        // 1. Global Bypass: View All
        if ($user->can('pr.view-all')) {
            return true;
        }

        // 2. Ownership: Always allowed
        if ($this->security->isOwner($pr, $user)) {
            return true;
        }

        // 3. Jurisdictional Oversight (Unified Brain): GMs, Dept Heads, Purchasers, etc.
        // This engine ensures the Policy matches the DataTable (Scoper) perfectly.
        if ($this->scoper->hasJurisdiction($user, $pr)) {
            return true;
        }

        // 4. Workflow Involvement: Anyone who has acted or is currently assigned to this PR.
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

        // Determine Authority
        $isOwner = $this->security->isOwner($pr, $user);
        $hasOversight = $this->scoper->hasJurisdiction($user, $pr);

        if (! $isOwner && ! $hasOversight) {
            return false;
        }

        // Validate Business State
        return $this->security->isStatusEditable($pr, $hasOversight);
    }

    /**
     * Determine whether the user can update the PO number.
     */
    public function updatePo(User $user, PurchaseRequest $pr): bool
    {
        if (! $user->can('pr.edit')) {
            return false;
        }

        if ($pr->is_cancel) {
            return false;
        }

        // Determine Authority
        $isOwner = $this->security->isOwner($pr, $user);
        $hasOversight = $this->scoper->hasJurisdiction($user, $pr);

        if (! $isOwner && ! $hasOversight) {
            return false;
        }

        // Validate Business State
        return $this->security->isStatusPoEditable($pr);
    }

    /**
     * Determine whether the user can delete the model (Soft Delete).
     */
    public function delete(User $user, PurchaseRequest $pr): bool
    {
        if (! $user->can('pr.delete')) {
            return false;
        }

        // Determine Authority
        $isOwner = $this->security->isOwner($pr, $user);
        $hasOversight = $this->scoper->hasJurisdiction($user, $pr);

        if (! $isOwner && ! $hasOversight) {
            return false;
        }

        // Validate Business State (Creators can delete drafts/rejected, oversight can delete almost anything active)
        return $this->security->isStatusEditable($pr, $hasOversight);
    }

    /**
     * Determine whether the user can cancel the model.
     */
    public function cancel(User $user, PurchaseRequest $pr): bool
    {
        if (! $user->can('pr.cancel')) {
            return false;
        }

        // Determine Authority
        $isOwner = $this->security->isOwner($pr, $user);
        $hasOversight = $this->scoper->hasJurisdiction($user, $pr);

        if (! $isOwner && ! $hasOversight) {
            return false;
        }

        // Validate Business State
        return $this->security->isStatusCancellable($pr);
    }

    /**
     * Determine if a user can perform the initial "Sign & Submit" action.
     */
    public function submit(User $user, PurchaseRequest $pr): bool
    {
        if (! $user->can('pr.create')) {
            return false;
        }

        // Only the creator can submit the initial draft
        if (! $this->security->isOwner($pr, $user)) {
            return false;
        }

        // Validate Business State
        return $this->security->canSubmit($pr);
    }

    /**
     * Determine if the user can see sensitive pricing information.
     */
    public function viewPrices(User $user, PurchaseRequest $pr): bool
    {
        // 1. Module Bypass
        if ($user->can('pr.view-prices') || $user->can('pr.admin')) {
            return true;
        }

        // 2. Ownership & Oversight
        $isOwner = $this->security->isOwner($pr, $user);
        $hasOversight = $this->scoper->hasJurisdiction($user, $pr);

        return $this->security->isSensitiveDataVisible($pr, $hasOversight, $isOwner);
    }

    /**
     * Determine if user can approve the PR (Security bridge to workflow).
     */
    public function approve(User $user, PurchaseRequest $pr): bool
    {
        return $user->can('pr.approve');
    }

    /**
     * Determine if user can reject the PR.
     */
    public function reject(User $user, PurchaseRequest $pr): bool
    {
        return $user->can('pr.reject');
    }

    /**
     * Determine if user can print the PR.
     */
    public function print(User $user, PurchaseRequest $pr): bool
    {
        return $user->can('pr.print') || $this->view($user, $pr);
    }

    /**
     * Determine if the user is eligible for auto-approval.
     */
    public function autoApprove(User $user, PurchaseRequest $pr): bool
    {
        return $this->security->canAutoApprove($pr, $user);
    }

    /**
     * Determine if user can perform batch actions.
     */
    public function batchApprove(User $user): bool
    {
        return $user->can('pr.batch-approve');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PurchaseRequest $pr): bool
    {
        return $user->can('pr.delete-forever');
    }
}
