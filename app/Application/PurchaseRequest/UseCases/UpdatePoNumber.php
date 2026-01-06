<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\UpdatePoNumberDTO;
use App\Events\PurchaseRequestPoNumberUpdated;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;

final class UpdatePoNumber
{
    public function __construct(
        private readonly \App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository $repo
    ) {}

    public function handle(UpdatePoNumberDTO $dto): PurchaseRequest
    {
        return DB::transaction(function () use ($dto) {
            $pr = $this->repo->find($dto->purchaseRequestId);
            
            if (! $pr) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Purchase Request not found");
            }

            // Only allow PO number update if PR is approved
            if ($pr->status !== 4) {
                throw new \DomainException('PO Number can only be updated for approved Purchase Requests');
            }

            // Update PO number
            $pr->update([
                'po_number' => $dto->poNumber,
                'updated_at' => now(),
            ]);

            // Dispatch event
            PurchaseRequestPoNumberUpdated::dispatch($pr, $dto->updatedByUserId);

            return $pr->fresh();
        });
    }
}
