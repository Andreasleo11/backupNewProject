<?php

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Domain\PurchaseRequest\Services\PurchaseRequestItemValidationService;
use App\Infrastructure\Persistence\Eloquent\Models\ApprovalStep;
use App\Models\PurchaseRequest;

final class ApprovePurchaseRequest
{
    public function __construct(
        private readonly Approvals $approvals,
        private readonly \App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository $repo,
        private readonly PurchaseRequestItemValidationService $itemValidator
    ) {}

    public function handle(ApprovalActionDTO $dto): void
    {
        $pr = $this->repo->find($dto->purchaseRequestId);

        if (! $pr) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Purchase Request not found');
        }

        // Ensure relations are loaded for the engine
        $this->repo->loadForApprovalContext($pr);

        // NEW: Validate item approvals if applicable at current workflow step
        $currentStep = $this->getCurrentStep($pr);

        if ($currentStep) {
            $approverType = $this->itemValidator->getApproverTypeFromStep($currentStep);

            if ($approverType) {
                // Validate that all items have been reviewed
                $validation = $this->itemValidator->validateForPrApproval($pr, $approverType);

                if (! $validation->isValid()) {
                    throw new \DomainException($validation->getMessage());
                }

                // Check if all items were rejected → auto-reject PR
                if (! $this->itemValidator->hasApprovedItems($pr, $approverType)) {
                    // All items rejected, reject the PR instead of approving
                    $this->approvals->reject(
                        $pr,
                        $dto->actorUserId,
                        'PR rejected: All items were rejected during review.'
                    );

                    // Status now computed from approvalRequest
                    $this->repo->loadForApprovalContext($pr);

                    return; // Exit early, don't approve
                }
            }
        }

        // Proceed with normal approval
        $this->approvals->approve($pr, $dto->actorUserId, $dto->remarks);

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
