<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Events\PurchaseRequestDeleted;
use Illuminate\Support\Facades\DB;

final class DeletePurchaseRequest
{
    public function __construct(
        private readonly \App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository $repo
    ) {}

    public function handle(int $purchaseRequestId, int $deletedByUserId): bool
    {
        return DB::transaction(function () use ($purchaseRequestId, $deletedByUserId) {
            $pr = $this->repo->find($purchaseRequestId);

            if (! $pr) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Purchase Request not found');
            }

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
