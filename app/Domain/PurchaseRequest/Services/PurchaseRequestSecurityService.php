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
        if ($user->is_gm) {
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
     * Determine if a user's role/attributes allow viewing sensitive data (like prices/master data).
     * This is a domain-level "Visibility Context" rule.
     */
    public function hasSensitiveVisibility(User $user): bool
    {
        // In the future, this can check specialized sub-departments 
        // without knowing about Spatie permissions.
        return $user->is_gm;
    }
}
