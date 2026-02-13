<?php

declare(strict_types=1);

namespace App\Policies;

use App\Application\Approval\Contracts\Approvals;
use App\Models\DetailPurchaseRequest;
use App\Infrastructure\Persistence\Eloquent\Models\User;

/**
 * Policy for item-level approval authorization.
 * 
 * **Simplified Logic:**
 * - Delegates to ApprovalEngine::canAct() to check PR-level authorization
 * - If user can approve the PR, they can approve/reject items
 * - No complex department-specific rules needed
 */
class PurchaseRequestItemPolicy
{
    public function __construct(
        private readonly Approvals $approvals
    ) {}

    /**
     * Determine whether the user can approve the item.
     */
    public function approve(User $user, DetailPurchaseRequest $item): bool
    {
        return $this->canActOnItem($user, $item);
    }

    /**
     * Determine whether the user can reject the item.
     */
    public function reject(User $user, DetailPurchaseRequest $item): bool
    {
        return $this->canActOnItem($user, $item);
    }

    /**
     * Check if user can act on item (approve or reject).
     * Delegates to ApprovalEngine authorization for the parent PR.
     */
    private function canActOnItem(User $user, DetailPurchaseRequest $item): bool
    {
        $pr = $item->purchaseRequest;

        if (!$pr || $pr->is_cancel) {
            return false;
        }

        // Delegate to ApprovalEngine - if user can approve PR, they can act on items
        if (!$this->approvals->canAct($pr, $user->id)) {
            return false;
        }

        // Get current workflow step
        $approval = $pr->approvalRequest;
        
        if (!$approval || $approval->status !== 'IN_REVIEW') {
            return false;
        }

        $currentStep = $approval->steps()
            ->where('sequence', $approval->current_step)
            ->first();

        // Check if this step supports item approval
        if (!$currentStep || !$currentStep->item_approver_type) {
            return false;
        }

        // Check if item hasn't been reviewed yet
        $column = $this->getApprovalColumn($currentStep->item_approver_type);
        
        return is_null($item->$column);
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
            default => throw new \InvalidArgumentException("Invalid approver type: {$approverType}"),
        };
    }
}
