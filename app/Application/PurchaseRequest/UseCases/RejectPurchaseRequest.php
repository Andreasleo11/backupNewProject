<?php

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Domain\PurchaseRequest\Services\PurchaseRequestItemValidationService;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Models\PurchaseRequest;

final class RejectPurchaseRequest
{
    public function __construct(
        private readonly Approvals $approvals,
        private readonly \App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository $repo,
        private readonly PurchaseRequestItemValidationService $itemValidator,
    ) {}

    public function handle(ApprovalActionDTO $dto): void
    {
        $pr = $this->repo->find($dto->purchaseRequestId);

        if (! $pr) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Purchase Request not found');
        }

        // Ensure relations are loaded
        $this->repo->loadForApprovalContext($pr);

        // Auto-reject any still-pending item-level approvals so records stay consistent.
        // Mirrors what ApprovePurchaseRequest does on the approve path.
        if ($dto->autoApproveItems) {
            $currentStep = $this->getCurrentStep($pr);

            if ($currentStep) {
                $approverType = $this->itemValidator->getApproverTypeFromStep($currentStep);

                if ($approverType) {
                    $this->itemValidator->autoRejectPendingItems($pr, $approverType);
                    $pr->load('itemDetail');
                }
            }
        }

        $this->approvals->reject($pr, $dto->actorUserId, $dto->remarks);

        // Reload fresh state (workflow_status computed from approvalRequest)
        $this->repo->loadForApprovalContext($pr);
    }

    /**
     * Get the current approval step for the PR.
     */
    private function getCurrentStep(PurchaseRequest $pr): ?ApprovalStep
    {
        $approval = $pr->approvalRequest;

        if (! $approval) {
            return null;
        }

        return $approval->steps()
            ->where('sequence', $approval->current_step)
            ->first();
    }
}
