<?php

namespace App\Jobs;

use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Application\PurchaseRequest\UseCases\RejectPurchaseRequest;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPurchaseRequestRejection implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $purchaseRequestId,
        public readonly int $actorUserId,
        public readonly string $rejectionReason
    ) {}

    public function handle(RejectPurchaseRequest $useCase): void
    {
        if ($this->batch()?->canceled()) {
            return;
        }

        $dto = new ApprovalActionDTO(
            purchaseRequestId: $this->purchaseRequestId,
            actorUserId: $this->actorUserId,
            remarks: $this->rejectionReason
        );

        $useCase->handle($dto);
    }
}
