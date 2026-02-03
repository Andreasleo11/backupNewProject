<?php

declare(strict_types=1);

namespace App\Domain\PurchaseRequest\Services;

use App\Domain\PurchaseRequest\ValueObjects\WorkflowStep;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\DetailPurchaseRequest;
use App\Models\PurchaseRequest;

/**
 * Domain Service for item-level authorization.
 * Encapsulates business rules about who can approve/reject items.
 */
final class ItemApprovalAuthorizationService
{
    /**
     * Check if a user can approve or reject an item.
     */
    public function canApproveOrReject(User $user, DetailPurchaseRequest $item): bool
    {
        $pr = $item->purchaseRequest;

        if (! $pr) {
            return false;
        }

        // Get current workflow step
        $approval = $pr->approvalRequest;
        if (! $approval) {
            return false;
        }

        $currentStepData = $approval->steps()->where('sequence', $approval->current_step)->first();
        if (! $currentStepData) {
            return false;
        }

        $workflowStep = WorkflowStep::fromRoleSlug($currentStepData->approver_snapshot_role_slug);

        if (! $workflowStep || ! $workflowStep->requiresItemApproval()) {
            return false;
        }

        // Check user authorization based on workflow step
        return match ($workflowStep->approverType()) {
            'head' => $this->canActAsDeptHead($user, $pr),
            'verificator' => $this->canActAsVerificator($user),
            'director' => $this->canActAsDirector($user),
            default => false,
        };
    }

    /**
     * Get the workflow step for a purchase request.
     */
    public function getCurrentWorkflowStep(PurchaseRequest $pr): ?WorkflowStep
    {
        $approval = $pr->approvalRequest;
        if (! $approval) {
            return null;
        }

        $currentStepData = $approval->steps()->where('sequence', $approval->current_step)->first();
        if (! $currentStepData) {
            return null;
        }

        return WorkflowStep::fromRoleSlug($currentStepData->approver_snapshot_role_slug);
    }

    /**
     * Business rule: Can user act as department head?
     */
    private function canActAsDeptHead(User $user, PurchaseRequest $pr): bool
    {
        // Must be a department head
        if (! $user->is_head) {
            return false;
        }

        // Special case: PERSONALIA department requires matching department
        if ($pr->from_department === 'PERSONALIA') {
            return $user->department?->name === 'PERSONALIA';
        }

        // Special case: STORE department allows any head
        if ($pr->from_department === 'STORE') {
            return true;
        }

        // Default: must be head
        return true;
    }

    /**
     * Business rule: Can user act as verificator?
     */
    private function canActAsVerificator(User $user): bool
    {
        return $user->specification?->name === 'VERIFICATOR';
    }

    /**
     * Business rule: Can user act as director?
     */
    private function canActAsDirector(User $user): bool
    {
        return $user->specification?->name === 'DIRECTOR';
    }
}
