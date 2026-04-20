<?php

namespace App\Domain\PurchaseRequest\Services;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\PurchaseRequest;

/**
 * Domain Service for calculating business rules related to Purchase Request security.
 * This service focuses strictly on domain logic (e.g., status-based rules, department rules).
 */
final class PurchaseRequestSecurityService
{
    /**
     * Determine if a PR is eligible for auto-approval (using a digital autograph).
     */
    public function canAutoApprove(PurchaseRequest $pr, User $user): bool
    {
        // 1. Module-wide Permissions
        if ($user->can('pr.auto-approve')) {
            return true;
        }

        // 2. Special Department Rules (e.g., Moulding)
        if ($pr->from_department === 'MOULDING') {
            return true;
        }

        return false;
    }

    /**
     * Determine if the PR status allows for data updates.
     * Note: Jurisdiction is handled by the Policy layer.
     */
    public function isStatusEditable(PurchaseRequest $pr, bool $hasOversight = false): bool
    {
        $status = strtoupper($pr->workflow_status ?? 'DRAFT');

        // Creators/Owners can only edit in "Initial" or "Returned" states
        if (! $hasOversight) {
            $allowedForCreator = ['DRAFT', 'RETURNED', 'REJECTED'];

            return in_array($status, $allowedForCreator);
        }

        // Users with jurisdictional oversight can edit in almost any active state
        $allowedForOversight = ['DRAFT', 'RETURNED', 'REJECTED', 'IN_REVIEW', 'APPROVED'];

        return in_array($status, $allowedForOversight);
    }

    /**
     * Determine if the PR status allows for cancellation.
     */
    public function isStatusCancellable(PurchaseRequest $pr): bool
    {
        $status = strtoupper($pr->workflow_status ?? 'DRAFT');

        // Cannot cancel if already finished or cancelled
        return ! in_array($status, ['APPROVED', 'CANCELED', 'REJECTED']);
    }

    /**
     * Determine if the PR status allows for PO Number updates.
     */
    public function isStatusPoEditable(PurchaseRequest $pr): bool
    {
        $status = strtoupper($pr->workflow_status ?? 'DRAFT');

        // PO Number can only be updated for approved requests
        return $status === 'APPROVED';
    }

    /**
     * Determine if a user can perform the initial "Sign & Submit" action.
     */
    public function canSubmit(PurchaseRequest $pr): bool
    {
        $status = strtoupper($pr->workflow_status ?? 'DRAFT');
        $allowedStatuses = ['DRAFT', 'RETURNED', 'REJECTED'];

        return in_array($status, $allowedStatuses);
    }

    /**
     * Determine if a user's role/attributes allow viewing sensitive data.
     */
    public function isSensitiveDataVisible(PurchaseRequest $pr, bool $hasJurisdiction = false, bool $isOwner = false): bool
    {
        return $hasJurisdiction || $isOwner;
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

        return $dept === 'MOULDING';
    }
}
