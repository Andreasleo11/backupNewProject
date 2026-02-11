<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\ItemApprovalActionDTO;
use App\Domain\PurchaseRequest\Services\ItemApprovalAuthorizationService;
use App\Models\DetailPurchaseRequest;

/**
 * Use Case: Reject a purchase request item.
 */
final class RejectItem
{
    public function __construct(
        private readonly ItemApprovalAuthorizationService $authService
    ) {}

    public function handle(ItemApprovalActionDTO $dto): void
    {
        $item = DetailPurchaseRequest::findOrFail($dto->itemId);
        $user = $dto->actorUser;

        // Authorization check
        if (! $this->authService->canApproveOrReject($user, $item)) {
            throw new \DomainException('You are not authorized to reject this item.');
        }

        // Get workflow step to determine which field to update
        $workflowStep = $this->authService->getCurrentWorkflowStep($item->purchaseRequest);

        if (! $workflowStep) {
            throw new \DomainException('Could not determine workflow step.');
        }

        $approverType = $workflowStep->approverType();

        if (! $approverType) {
            throw new \DomainException('This workflow step does not support item rejection.');
        }

        // Update appropriate field based on approver type
        $updates = match ($approverType) {
            'head' => ['is_approve_by_head' => false],
            'gm' => ['is_approve_by_gm' => false],
            'verificator' => ['is_approve_by_verificator' => false],
            'director' => ['is_approve' => false],
            default => throw new \DomainException("Invalid approver type: {$approverType}"),
        };

        $item->update($updates);
    }
}
