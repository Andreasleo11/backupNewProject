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

            // Authorization: Only creator can delete
            if ($pr->user_id_create !== $deletedByUserId) {
                throw new \Illuminate\Auth\Access\AuthorizationException('You are not authorized to delete this purchase request');
            }

            // Validation: Only Draft can be deleted
            if ($pr->status !== 8 || $pr->workflow_status !== 'DRAFT') {
                if ($pr->status === 4) {
                    throw new \DomainException('Cannot delete an approved Purchase Request');
                }
                throw new \DomainException('Only draft purchase requests can be deleted');
            }

            // Soft delete items first (cascade)
            \App\Models\DetailPurchaseRequest::where('purchase_request_id', $purchaseRequestId)->delete();

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
