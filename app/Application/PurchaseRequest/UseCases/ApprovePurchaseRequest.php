<?php

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Models\PurchaseRequest; // sementara pakai model existing

final class ApprovePurchaseRequest
{
    public function __construct(private readonly Approvals $approvals) {}

    public function handle(ApprovalActionDTO $dto): void
    {
        /** @var PurchaseRequest $pr */
        $pr = PurchaseRequest::query()
            ->with(['approvalRequest.steps']) // biar engine ga N+1
            ->findOrFail($dto->purchaseRequestId);

        $this->approvals->approve($pr, $dto->actorUserId, $dto->remarks);
    }
}
