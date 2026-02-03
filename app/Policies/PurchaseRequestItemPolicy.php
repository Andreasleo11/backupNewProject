<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\PurchaseRequest\Services\ItemApprovalAuthorizationService;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\DetailPurchaseRequest;

/**
 * Laravel Policy for item approval authorization.
 * Delegates to domain service for business logic.
 */
class PurchaseRequestItemPolicy
{
    public function __construct(
        private readonly ItemApprovalAuthorizationService $authService
    ) {}

    /**
     * Determine if user can approve the item.
     */
    public function approve(User $user, DetailPurchaseRequest $item): bool
    {
        return $this->authService->canApproveOrReject($user, $item);
    }

    /**
     * Determine if user can reject the item.
     */
    public function reject(User $user, DetailPurchaseRequest $item): bool
    {
        return $this->authService->canApproveOrReject($user, $item);
    }
}
