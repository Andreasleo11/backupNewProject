<?php

namespace App\Jobs;

use App\Application\PurchaseRequest\DTOs\ApprovalActionDTO;
use App\Application\PurchaseRequest\UseCases\ApprovePurchaseRequest;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPurchaseRequestApproval implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $purchaseRequestId,
        public readonly int $actorUserId,
        public readonly ?string $remarks = null
    ) {}

    public function handle(ApprovePurchaseRequest $useCase): void
    {
        if ($this->batch()?->canceled()) {
            return;
        }

        $dto = new ApprovalActionDTO(
            purchaseRequestId: $this->purchaseRequestId,
            actorUserId: $this->actorUserId,
            remarks: $this->remarks
        );

        $useCase->handle($dto);
    }
}
