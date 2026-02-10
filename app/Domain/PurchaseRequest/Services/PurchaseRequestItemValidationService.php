<?php

namespace App\Domain\PurchaseRequest\Services;

use App\Domain\PurchaseRequest\DTOs\ValidationResult;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\PurchaseRequest;

/**
 * Service for validating item-level approvals in Purchase Requests.
 *
 * This service integrates line-item approvals with workflow-based approvals
 * by ensuring all items are reviewed before the PR can be approved.
 */
class PurchaseRequestItemValidationService
{
    /**
     * Check if all items have been reviewed by the specified approver type.
     *
     * @param string $approverType One of: 'head', 'verificator', 'director'
     */
    public function allItemsReviewed(PurchaseRequest $pr, string $approverType): bool
    {
        $items = $pr->itemDetail;

        if ($items->isEmpty()) {
            return true; // No items to review
        }

        $column = $this->getApprovalColumn($approverType);

        // Check if all items have a decision (not null)
        return $items->every(fn ($item) => ! is_null($item->$column));
    }

    /**
     * Check if at least one item is approved by the specified approver type.
     */
    public function hasApprovedItems(PurchaseRequest $pr, string $approverType): bool
    {
        $items = $pr->itemDetail;

        if ($items->isEmpty()) {
            return false;
        }

        $column = $this->getApprovalColumn($approverType);

        // Check if at least one item is approved (value = 1)
        return $items->contains(fn ($item) => $item->$column === 1);
    }

    /**
     * Get item review statistics for the specified approver type.
     *
     * @return array ['total' => int, 'approved' => int, 'rejected' => int, 'pending' => int]
     */
    public function getItemStats(PurchaseRequest $pr, string $approverType): array
    {
        $items = $pr->itemDetail;
        $column = $this->getApprovalColumn($approverType);

        $total = $items->count();
        $approved = $items->where($column, 1)->count();
        $rejected = $items->where($column, 0)->count();
        $pending = $items->whereNull($column)->count();

        return [
            'total' => $total,
            'approved' => $approved,
            'rejected' => $rejected,
            'pending' => $pending,
        ];
    }

    /**
     * Check if the user can review items at the current workflow step.
     */
    public function canReviewItems(User $user, PurchaseRequest $pr): bool
    {
        // Check if PR has active workflow
        $approval = $pr->approvalRequest;
        if (! $approval || $approval->status !== 'IN_REVIEW') {
            return false;
        }

        // Get current step
        $currentStep = $approval->steps()
            ->where('sequence', $approval->current_step)
            ->first();

        if (! $currentStep) {
            return false;
        }

        // Check if user is the current approver
        if ($currentStep->approver_type === 'user' && $currentStep->approver_id !== $user->id) {
            return false;
        }

        if ($currentStep->approver_type === 'role') {
            $role = \Spatie\Permission\Models\Role::find($currentStep->approver_id);
            if (! $role || ! $user->hasRole($role->name)) {
                return false;
            }
        }

        // Check if this step allows item reviews
        $approverType = $this->getApproverTypeFromStep($currentStep);

        return $approverType !== null;
    }

    /**
     * Validate if PR can be approved based on item review status.
     */
    public function validateForPrApproval(PurchaseRequest $pr, string $approverType): ValidationResult
    {
        $stats = $this->getItemStats($pr, $approverType);

        // Check if all items have been reviewed
        if ($stats['pending'] > 0) {
            return ValidationResult::failure(
                "You must approve or reject all {$stats['pending']} pending item(s) before approving this Purchase Request.",
                $stats
            );
        }

        // Check if at least one item is approved
        if ($stats['approved'] === 0) {
            return ValidationResult::failure(
                'Cannot approve PR: All items have been rejected.',
                $stats
            );
        }

        return ValidationResult::success('All items reviewed, ready for approval.');
    }

    /**
     * Get the approval column name for the specified approver type.
     *
     * @throws \InvalidArgumentException
     */
    private function getApprovalColumn(string $approverType): string
    {
        return match ($approverType) {
            'head' => 'is_approve_by_head',
            'verificator' => 'is_approve_by_verificator',
            'director' => 'is_approve',
            'purchaser' => 'is_approve_by_head',
            default => throw new \InvalidArgumentException("Invalid approver type: {$approverType}"),
        };
    }

    /**
     * Map approval step to approver type for item reviews.
     * Returns null if the step does not support item reviews.
     *
     * @return string|null One of: 'head', 'verificator', 'director', or null
     */
    public function getApproverTypeFromStep(ApprovalStep $step): ?string
    {
        return $step->item_approver_type;
    }
}
