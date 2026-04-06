<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\CancelPurchaseRequestDTO;
use App\Events\PurchaseRequestCancelled;
use App\Models\PurchaseRequest;
use Illuminate\Support\Facades\DB;

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

            // Authorization: Only creator or super-admin can cancel
            $user = \App\Models\User::find($dto->cancelledByUserId);
            $isCreator = $pr->user_id_create === (int) $dto->cancelledByUserId;
            $isSuperAdmin = $user && $user->hasRole('super-admin');

            if (! $isCreator && ! $isSuperAdmin) {
                throw new \Illuminate\Auth\Access\AuthorizationException('You are not authorized to cancel this purchase request');
            }

            // Validate that PR can be cancelled (Cannot cancel if already approved or rejected)
            if ($pr->workflow_status === 'APPROVED') {
                throw new \DomainException('Cannot cancel an approved Purchase Request');
            }

            if ($pr->workflow_status === 'REJECTED') {
                throw new \DomainException('Cannot cancel a rejected Purchase Request');
            }

            if ($pr->is_cancel) {
                throw new \DomainException('Purchase Request is already cancelled');
            }

            // Update PR to cancelled state
            $pr->update([
                'is_cancel' => true,
                'description' => $dto->reason,
                'updated_at' => now(),
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
