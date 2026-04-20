<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\CancelPurchaseRequestDTO;
use App\Events\PurchaseRequestCancelled;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class CancelPurchaseRequest
{
    public function __construct(
        private readonly \App\Domain\PurchaseRequest\Repositories\PurchaseRequestRepository $repo
    ) {}

    public function handle(CancelPurchaseRequestDTO $dto): PurchaseRequest
    {
        return DB::transaction(function () use ($dto) {
            $pr = $this->repo->find($dto->purchaseRequestId);

            if (! $pr) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Purchase Request not found');
            }

            // Authorization & Validation (Delegated to Policy)
            Gate::authorize('cancel', $pr);

            // Double Check: Ensure it wasn't already cancelled by another process
            if ($pr->is_cancel) {
                throw new \DomainException('Purchase Request is already cancelled');
            }

            // Update PR to cancelled state
            $pr->update([
                'is_cancel' => true,
                'description' => $dto->reason,
            ]);

            // Cancel the approval workflow if present
            if ($pr->approvalRequest) {
                $pr->approvalRequest->update(['status' => 'CANCELED']);
            }

            // Dispatch event
            PurchaseRequestCancelled::dispatch($pr);

            return $pr->fresh();
        });
    }
}
