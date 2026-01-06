<?php

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\Contracts\SyncPrWorkflow;
use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;

final class RejectPurchaseRequest
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

        // Ensure relations are loaded
        $this->repo->loadForApprovalContext($pr);

        $this->approvals->reject($pr, $dto->actorUserId, $dto->remarks);

        $this->repo->loadForApprovalContext($pr);
        $this->syncPrWorkflow->sync($pr);
    }
}
