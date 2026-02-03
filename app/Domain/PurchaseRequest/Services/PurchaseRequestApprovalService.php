<?php

declare(strict_types=1);

namespace App\Domain\PurchaseRequest\Services;

use App\Models\DetailPurchaseRequest;
use App\Models\PurchaseRequest;

final class PurchaseRequestApprovalService
{
    public function __construct(
        private readonly PurchaseRequestItemValidationService $itemValidator
    ) {}

    /**
     * Approve detail by role type.
     */
    public function approveDetail(int $detailId, string $type): void
    {
        $detail = DetailPurchaseRequest::findOrFail($detailId);
        $pr = $detail->purchaseRequest;

        // Validate that the current workflow step allows this type of approval
        $this->validateWorkflowStep($pr, $type);

        $updates = match ($type) {
            'head' => ['is_approve_by_head' => true],
            'verificator' => ['is_approve_by_verificator' => true],
            'director' => ['is_approve' => true],
            default => [],
        };

        if (! empty($updates)) {
            $detail->update($updates);
        }
    }

    /**
     * Reject detail by role type.
     */
    public function rejectDetail(int $detailId, string $type): void
    {
        $detail = DetailPurchaseRequest::findOrFail($detailId);
        $pr = $detail->purchaseRequest;

        // Validate that the current workflow step allows this type of rejection
        $this->validateWorkflowStep($pr, $type);

        $updates = match ($type) {
            'head' => ['is_approve_by_head' => false],
            'verificator' => ['is_approve_by_verificator' => false],
            'director' => ['is_approve' => false],
            default => [],
        };

        if (! empty($updates)) {
            $detail->update($updates);
        }
    }

    /**
     * Validate that the PR is at the correct workflow step for this approval type.
     */
    private function validateWorkflowStep(PurchaseRequest $pr, string $type): void
    {
        $user = auth()->user();

        if (! $user) {
            throw new \DomainException('User must be authenticated to approve items.');
        }

        // Check if user can review items at current workflow step
        if (! $this->itemValidator->canReviewItems($user, $pr)) {
            throw new \DomainException('You are not authorized to review items at this workflow step.');
        }

        // Additionally validate that the type matches the current step
        $approval = $pr->approvalRequest;
        if (! $approval) {
            throw new \DomainException('This purchase request does not have an approval workflow.');
        }

        $currentStep = $approval->steps()->where('sequence', $approval->current_step)->first();
        if (! $currentStep) {
            throw new \DomainException('Could not determine current workflow step.');
        }

        // Map step to expected type
        $expectedType = match ($currentStep->approver_snapshot_role_slug) {
            'pr-dept-head-office', 'pr-dept-head-factory' => 'head',
            'pr-verificator-computer', 'pr-verificator-personalia' => 'verificator',
            'pr-director' => 'director',
            default => null,
        };

        if ($expectedType !== $type) {
            throw new \DomainException("Invalid approval type '{$type}' for current workflow step.");
        }
    }

    /**
     * Batch approve purchase requests.
     */
    public function batchApprove(array $ids, string $username, string $imageUrl): array
    {
        if (empty($ids)) {
            return [
                'success' => false,
                'message' => 'No records selected for approval.',
            ];
        }

        try {
            foreach ($ids as $id) {
                $this->approveSingleRequest($id, $username, $imageUrl);
            }

            return [
                'success' => true,
                'message' => 'Selected records approved successfully.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to approve selected records.',
            ];
        }
    }

    /**
     * Batch reject purchase requests.
     */
    public function batchReject(array $ids, string $rejectionReason): array
    {
        if (empty($ids)) {
            return [
                'success' => false,
                'message' => 'No records selected for rejection.',
            ];
        }

        try {
            foreach ($ids as $id) {
                $this->rejectSingleRequest($id, $rejectionReason);
            }

            return [
                'success' => true,
                'message' => 'Selected records rejected successfully.',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Failed to reject selected records.',
            ];
        }
    }

    /**
     * Approve single purchase request.
     */
    private function approveSingleRequest(int $id, string $username, string $imageUrl): void
    {
        $pr = PurchaseRequest::find($id);

        // Add signature using new signature system
        $signatureService = app(\App\Domain\PurchaseRequest\Services\PurchaseRequestSignatureService::class);
        $user = \App\Models\User::where('name', $username)->first();

        if ($user) {
            $signatureService->addSignature($pr, 'DIRECTOR', $user->id, $imageUrl);
        }

        $pr->update([
            'status' => 4,
            'approved_at' => now(),
        ]);

        // Determine which details to approve based on type
        $computerFactory = $pr->to_department->value === 'COMPUTER' && $pr->type === 'factory';

        if ($pr->type === 'factory' && ! $computerFactory) {
            $details = DetailPurchaseRequest::where('purchase_request_id', $id)
                ->where('is_approve_by_gm', 1)
                ->get();
        } else {
            $details = DetailPurchaseRequest::where('purchase_request_id', $id)
                ->where('is_approve_by_verificator', 1)
                ->get();
        }

        foreach ($details as $detail) {
            $detail->update(['is_approve' => 1]);
        }
    }

    /**
     * Reject single purchase request.
     */
    private function rejectSingleRequest(int $id, string $rejectionReason): void
    {
        $pr = PurchaseRequest::find($id);

        $pr->update([
            'status' => 5,
            'description' => $rejectionReason,
        ]);

        // Determine which details to reject based on type
        $computerFactory = $pr->to_department->value === 'COMPUTER' && $pr->type === 'factory';

        if ($pr->type === 'factory' && ! $computerFactory) {
            $details = DetailPurchaseRequest::where('purchase_request_id', $id)
                ->where('is_approve_by_gm', 1)
                ->get();
        } else {
            $details = DetailPurchaseRequest::where('purchase_request_id', $id)
                ->where('is_approve_by_verificator', 1)
                ->get();
        }

        foreach ($details as $detail) {
            $detail->update(['is_approve' => 0]);
        }
    }
}
