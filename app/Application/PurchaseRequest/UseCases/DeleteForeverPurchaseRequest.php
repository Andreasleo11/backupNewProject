<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Events\PurchaseRequestDeleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class DeleteForeverPurchaseRequest
{
    public function __construct(
        private readonly \App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository $repo
    ) {}

    public function handle(int $purchaseRequestId, int $deletedByUserId): bool
    {
        return DB::transaction(function () use ($purchaseRequestId, $deletedByUserId) {
            // Find PR including soft-deleted ones
            $pr = \App\Models\PurchaseRequest::withTrashed()->find($purchaseRequestId);

            if (! $pr) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Purchase Request not found');
            }

            // Authorization (Delegated to Policy)
            Gate::authorize('forceDelete', $pr);

            // Force delete items first
            \App\Models\DetailPurchaseRequest::withTrashed()
                ->where('purchase_request_id', $purchaseRequestId)
                ->forceDelete();

            // Force delete the PR
            $deleted = $pr->forceDelete();

            if ($deleted) {
                // Dispatch event
                PurchaseRequestDeleted::dispatch($pr, $deletedByUserId);
            }

            return $deleted;
        });
    }
}
