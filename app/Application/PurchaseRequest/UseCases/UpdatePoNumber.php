<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\UpdatePoNumberDTO;
use App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository;
use App\Events\PurchaseRequestPoNumberUpdated;
use Illuminate\Support\Facades\DB;

final class UpdatePoNumber
{
    public function __construct(
        private readonly PurchaseRequestRepository $repo
    ) {}

    public function handle(UpdatePoNumberDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            $pr = $this->repo->find($dto->purchaseRequestId);

            if (! $pr) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Purchase Request not found');
            }

            // Only allow PO number update if PR is approved
            if ($pr->workflow_status !== 'APPROVED') {
                throw new \DomainException('PO Number can only be updated for approved Purchase Requests');
            }

            // Update PO number
            $this->repo->updatePoNumber($pr, $dto->poNumber);

            // Dispatch event
            PurchaseRequestPoNumberUpdated::dispatch($pr, $dto->updatedByUserId);
        });
    }
}
