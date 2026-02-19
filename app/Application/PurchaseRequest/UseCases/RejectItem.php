<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\DTOs\ItemApprovalActionDTO;
use App\Models\DetailPurchaseRequest;

/**
 * Use Case: Reject a purchase request item.
 *
 * **Simplified Authorization:**
 * - Uses ApprovalEngine::canAct() to check if user can approve the PR
 * - If yes, user can reject individual items
 * - No complex department-specific logic needed
 */
final class RejectItem
{
    public function __construct(
        private readonly Approvals $approvals
    ) {}

    public function handle(ItemApprovalActionDTO $dto): void
    {
        $item = DetailPurchaseRequest::findOrFail($dto->itemId);
        $pr = $item->purchaseRequest;
        $user = $dto->actorUser;

        if (! $pr) {
            throw new \DomainException('Item does not belong to a Purchase Request.');
        }

        if ($pr->is_cancel) {
            throw new \DomainException('Cannot reject items in a cancelled PR.');
        }

        // Authorization: Use ApprovalEngine to check if user can act on PR
        if (! $this->approvals->canAct($pr, $user->id)) {
            throw new \DomainException('You are not authorized to reject this item.');
        }

        // Get current workflow step to determine which column to update
        $approval = $pr->approvalRequest;

        if (! $approval || $approval->status !== 'IN_REVIEW') {
            throw new \DomainException('PR is not in review status.');
        }

        $currentStep = $approval->steps()
            ->where('sequence', $approval->current_step)
            ->first();

        if (! $currentStep || ! $currentStep->item_approver_type) {
            throw new \DomainException('This workflow step does not support item rejection.');
        }

        // Check if item is still pending (not already approved/rejected)
        $column = $this->getApprovalColumn($currentStep->item_approver_type);

        if (! is_null($item->$column)) {
            throw new \DomainException('This item has already been reviewed.');
        }

        // Update appropriate field (false = rejected)
        $item->update([$column => false]);
    }

    /**
     * Map approver type to database column.
     */
    private function getApprovalColumn(string $approverType): string
    {
        return match ($approverType) {
            'head', 'purchaser' => 'is_approve_by_head',
            'gm' => 'is_approve_by_gm',
            'verificator' => 'is_approve_by_verificator',
            'director' => 'is_approve',
            default => throw new \DomainException("Invalid approver type: {$approverType}"),
        };
    }
}
