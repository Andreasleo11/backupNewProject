<?php

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Models\PurchaseRequest;

final class RejectPurchaseRequest
{
    public function __construct(private readonly Approvals $approvals) {}

    public function handle(ApprovalActionDTO $dto): void
    {
        /** @var PurchaseRequest $pr */
        $pr = PurchaseRequest::query()
            ->with(['approvalRequest.steps'])
            ->findOrFail($dto->purchaseRequestId);

        $this->approvals->reject($pr, $dto->actorUserId, $dto->remarks);
    }
}
