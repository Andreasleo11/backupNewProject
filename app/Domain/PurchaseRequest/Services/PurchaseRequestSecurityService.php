<?php

namespace App\Domain\PurchaseRequest\Services;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\PurchaseRequest;

/**
 * Domain Service for calculating business rules related to Purchase Request security.
 * This service is "Infrastructure-Ignorant" and focuses strictly on domain logic
 * (e.g., department rules, creator rules).
 */
final class PurchaseRequestSecurityService
{
    public function __construct(
        private readonly \App\Infrastructure\Approval\Services\ApprovalScopingManager $scoper
    ) {}

    /**
     * Determine if a PR is eligible for auto-approval (using a digital autograph).
     */
    public function canAutoApprove(PurchaseRequest $pr, User $user): bool
    {
        // 1. Module-wide Permissions
        if ($user->can('pr.auto-approve')) {
            return true;
        }

        // 2. GM logic (Delegated to jurisdiction engine)
        if ($user->hasRole('general-manager') && $this->scoper->hasJurisdiction($user, $pr)) {
            return true;
        }

        // 3. Special Department Rules (e.g., Moulding)
        if ($pr->from_department === 'MOULDING') {
            return true;
        }

        return false;
    }

    /**
     * Determine if a user can update the model based on business status.
     */
    public function canUpdate(PurchaseRequest $pr, User $user): bool
    {
        $status = strtoupper($pr->workflow_status ?? 'DRAFT');

        // A. Creator: Can edit only in draft-like or rejected states
        if ($this->isOwner($pr, $user)) {
            $allowedForCreator = ['DRAFT', 'RETURNED', 'REJECTED'];
            if (in_array($status, $allowedForCreator)) {
                return true;
            }
        }

        // B. Jurisdictional Oversight (Purchasers/GMs/Admins/DeptHeads)
        if ($this->scoper->hasJurisdiction($user, $pr)) {
            $allowedForOversight = ['DRAFT', 'RETURNED', 'REJECTED', 'IN_REVIEW', 'APPROVED'];

            return in_array($status, $allowedForOversight);
        }

        return false;
    }

    /**
     * Determine if a user can cancel the model based on business status.
     */
    public function canCancel(PurchaseRequest $pr, User $user): bool
    {
        $status = strtoupper($pr->workflow_status ?? 'DRAFT');

        // Cannot cancel if already finished or cancelled
        if (in_array($status, ['APPROVED', 'CANCELED', 'REJECTED'])) {
            return false;
        }

        // 1. Creator can cancel their own
        if ($this->isOwner($pr, $user)) {
            return true;
        }

        // 2. Jurisdictional Oversight
        if ($this->scoper->hasJurisdiction($user, $pr)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if a user can perform the initial "Sign & Submit" action.
     */
    public function canSubmit(PurchaseRequest $pr, User $user): bool
    {
        // Only the creator can submit the initial draft
        if (! $this->isOwner($pr, $user)) {
            return false;
        }

        $status = strtoupper($pr->workflow_status ?? 'DRAFT');
        $allowedStatuses = ['DRAFT', 'RETURNED', 'REJECTED'];

        return in_array($status, $allowedStatuses);
    }

    /**
     * Determine if a user's role/attributes allow viewing sensitive data (like prices/master data).
     */
    public function canViewSensitiveData(User $user, PurchaseRequest $pr): bool
    {
        // 1. Jurisdictional Oversight (Admin, GM, Dept Head over this PR)
        if ($this->scoper->hasJurisdiction($user, $pr)) {
            return true;
        }

        // 2. Creators can see their own data
        if ($this->isOwner($pr, $user)) {
            return true;
        }

        return false;
    }

    /**
     * Determine if the "Import vs Local" selection should be visible.
     */
    public function canSelectImportPath(?string $fromDept, ?string $toDept): bool
    {
        if (! $fromDept || ! $toDept) {
            return false;
        }

        $from = strtoupper(trim($fromDept));
        $to = strtoupper(trim($toDept));

        return ($from === 'MOULDING' || $from === 'MOLDING')
            && ($to === strtoupper(\App\Enums\ToDepartment::PURCHASING->value));
    }

    /**
     * Check if the user is the creator of the request.
     */
    public function isOwner(PurchaseRequest $pr, User $user): bool
    {
        $creatorId = (int) ($pr->user_id_create ?? $pr->created_by);

        return (int) $user->id === $creatorId;
    }

    /**
     * Determine if the user's department is eligible for the import selection.
     */
    public function canUserSelectImportPath(User $user): bool
    {
        $dept = strtoupper(trim($user->department?->name ?? ''));

        return $dept === 'MOULDING' || $dept === 'MOLDING';
    }
}
