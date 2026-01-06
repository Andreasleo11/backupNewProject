<?php

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\Contracts\SyncPrWorkflow;
use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;

final class ApprovePurchaseRequest
{
    public function __construct(
        private readonly Approvals $approvals,
        private readonly SyncPrWorkflow $syncPrWorkflow,
        private readonly \App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository $repo
    ) {}

    public function handle(ApprovalActionDTO $dto): void
    {
        $pr = $this->repo->find($dto->purchaseRequestId);

        if (! $pr) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Purchase Request not found');
        }

        // Ensure relations are loaded for the engine
        $this->repo->loadForApprovalContext($pr);

        $this->approvals->approve($pr, $dto->actorUserId, $dto->remarks);

        // Reload fresh state if needed for sync (or repo->loadForApprovalContext might be enough)
        $this->repo->loadForApprovalContext($pr);
        $this->syncPrWorkflow->sync($pr);
    }
}
