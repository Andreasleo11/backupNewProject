<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\CancelPurchaseRequestDTO;
use App\Events\PurchaseRequestCancelled;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;

final class CancelPurchaseRequest
{
    public function handle(CancelPurchaseRequestDTO $dto): PurchaseRequest
    {
        return DB::transaction(function () use ($dto) {
            /** @var PurchaseRequest $pr */
            $pr = PurchaseRequest::findOrFail($dto->purchaseRequestId);

            // Validate that PR can be cancelled
            if ($pr->status === 4) {
                throw new \DomainException('Cannot cancel an approved Purchase Request');
            }

            if ($pr->status === 5) {
                throw new \DomainException('Cannot cancel a rejected Purchase Request');
            }

            // Update PR to cancelled state
            $pr->update([
                'is_cancel' => true,
                'description' => $dto->reason,
                'updated_at' => now(),
            ]);

            // Dispatch event
            PurchaseRequestCancelled::dispatch($pr);

            return $pr->fresh();
        });
    }
}
