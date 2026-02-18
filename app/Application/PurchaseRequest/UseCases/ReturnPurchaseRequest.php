<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\Approval\Contracts\Approvals;
use App\Application\PurchaseRequest\DTOs\ReturnPurchaseRequestDTO;
use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;

final class ReturnPurchaseRequest
{
    public function __construct(
        private readonly Approvals $approvals,
        private readonly PurchaseRequestRepository $repo
    ) {}

    public function handle(ReturnPurchaseRequestDTO $dto): void
    {
        $pr = $this->repo->find($dto->purchaseRequestId);

        if (! $pr) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Purchase Request not found');
        }

        // Ensure relations are loaded for the engine
        $this->repo->loadForApprovalContext($pr);

        $this->approvals->return($pr, $dto->actorUserId, $dto->reason);
        
        // Reload fresh state
        $this->repo->loadForApprovalContext($pr);
    }
}
