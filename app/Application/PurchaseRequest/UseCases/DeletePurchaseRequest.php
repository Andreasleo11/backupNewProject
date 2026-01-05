<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Events\PurchaseRequestDeleted;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;

final class DeletePurchaseRequest
{
    public function handle(int $purchaseRequestId, int $deletedByUserId): bool
    {
        return DB::transaction(function () use ($purchaseRequestId, $deletedByUserId) {
            /** @var PurchaseRequest $pr */
            $pr = PurchaseRequest::findOrFail($purchaseRequestId);

            // Validate that PR can be deleted
            if ($pr->status === 4) {
                throw new \DomainException('Cannot delete an approved Purchase Request');
            }

            // Soft delete the PR
            $deleted = $pr->delete();

            if ($deleted) {
                // Dispatch event
                PurchaseRequestDeleted::dispatch($pr, $deletedByUserId);
            }

            return $deleted;
        });
    }
}
