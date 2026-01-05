<?php

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\Contracts\SyncPrWorkflow;
use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Models\PurchaseRequest;

final class RejectPurchaseRequest
{
    public function __construct(
        private readonly Approvals $approvals,
        private readonly SyncPrWorkflow $syncPrWorkflow
    ) {}

    public function handle(ApprovalActionDTO $dto): void
    {
        /** @var PurchaseRequest $pr */
        $pr = PurchaseRequest::query()
            ->with(['approvalRequest.steps'])
            ->findOrFail($dto->purchaseRequestId);

        $this->approvals->reject($pr, $dto->actorUserId, $dto->remarks);

        $pr->load(['approvalRequest.steps']);
        $this->syncPrWorkflow->sync($pr);
    }
}
