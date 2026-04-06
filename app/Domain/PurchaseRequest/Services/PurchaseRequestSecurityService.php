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
    /**
     * Determine if a PR is eligible for auto-approval (using a digital autograph).
     */
    public function canAutoApprove(PurchaseRequest $pr, User $user): bool
    {
        // 1. GM logic (Moved from service)
        if ($user->hasRole('general-manager')) {
            return true;
        }

        // 2. Special Department Rules (e.g., Moulding)
        // This is a PURE domain rule based on department identification
        if ($pr->from_department === 'MOULDING') {
            return true;
        }

        return false;
    }

    /**
     * Determine if a user can update the model based on business status.
     * This separates the "Permission to Edit" from the "Ability to Edit".
     */
    public function canUpdate(PurchaseRequest $pr, User $user): bool
    {
        $status = strtoupper($pr->workflow_status ?? 'DRAFT');

        // A. Creator: Can edit only in draft-like or rejected states
        if ($user->id === (int) $pr->user_id_create) {
            $allowedForCreator = ['DRAFT', 'RETURNED', 'REJECTED'];
            if (in_array($status, $allowedForCreator)) {
                return true;
            }
        }

        // B. Oversight (Purchasers/GMs/Admins): Can edit even in workflow states
        // This is usually to fix master data (price, supplier) during review.
        if ($user->hasAnyRole(['purchaser', 'general-manager'])) {
            $allowedForOversight = ['DRAFT', 'RETURNED', 'REJECTED', 'IN_REVIEW', 'APPROVED'];
            if (in_array($status, $allowedForOversight)) {
                return true;
            }
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

        // Creator can cancel their own at any other time
        if ($user->id === (int) $pr->user_id_create) {
            return true;
        }

        // Oversight can cancel at any time
        if ($user->hasAnyRole(['purchaser', 'general-manager'])) {
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
        if ($user->id !== (int) $pr->user_id_create) {
            return false;
        }

        $status = strtoupper($pr->workflow_status ?? 'DRAFT');
        $allowedStatuses = ['DRAFT', 'RETURNED', 'REJECTED'];

        return in_array($status, $allowedStatuses);
    }

    /**
     * Determine if a user's role/attributes allow viewing sensitive data (like prices/master data).
     * This is a domain-level "Visibility Context" rule.
     */
    public function canViewSensitiveData(User $user, PurchaseRequest $pr): bool
    {
        // 1. Purchasers and Admins always see all data
        if ($user->can('pr.admin') || $user->hasRole('purchaser')) {
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
     * Currently restricted to Moulding to Purchasing requisitions.
     */
    public function canSelectImportPath(?string $fromDept, ?string $toDept): bool
    {
        if (!$fromDept || !$toDept) return false;

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
        return (int) $user->id === (int) ($pr->user_id_create ?? $pr->created_by);
    }

    /**
     * Determine if the user's department is eligible for the import selection.
     */
    public function canUserSelectImportPath(User $user): bool
    {
        $dept = strtoupper(trim($user->department?->name ?? ''));
        return ($dept === 'MOULDING' || $dept === 'MOLDING');
    }
}
