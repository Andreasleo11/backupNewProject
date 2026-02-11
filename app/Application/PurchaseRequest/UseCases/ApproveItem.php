<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\ItemApprovalActionDTO;
use App\Domain\PurchaseRequest\Services\ItemApprovalAuthorizationService;

use App\Models\DetailPurchaseRequest;

/**
 * Use Case: Approve a purchase request item.
 * Application layer orchestration of domain logic.
 */
final class ApproveItem
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
            throw new \DomainException('You are not authorized to approve this item.');
        }

        // Get workflow step to determine which field to update
        $workflowStep = $this->authService->getCurrentWorkflowStep($item->purchaseRequest);

        if (! $workflowStep) {
            throw new \DomainException('Could not determine workflow step.');
        }

        $approverType = $workflowStep->approverType();

        if (! $approverType) {
            throw new \DomainException('This workflow step does not support item approval.');
        }

        // Update appropriate field based on approver type
        $updates = match ($approverType) {
            'head' => ['is_approve_by_head' => true],
            'gm' => ['is_approve_by_gm' => true],
            'verificator' => ['is_approve_by_verificator' => true],
            'director' => ['is_approve' => true],
            default => throw new \DomainException("Invalid approver type: {$approverType}"),
        };

        $item->update($updates);
    }
}
