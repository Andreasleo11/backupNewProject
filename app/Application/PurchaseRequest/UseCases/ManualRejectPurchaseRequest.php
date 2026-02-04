<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\UseCases;

use App\Application\PurchaseRequest\DTOs\ManualRejectPurchaseRequestDTO;
use App\Models\PurchaseRequest;

final class ManualRejectPurchaseRequest
{
    public function handle(ManualRejectPurchaseRequestDTO $dto): void
    {
        $pr = PurchaseRequest::findOrFail($dto->purchaseRequestId);

        $pr->update([
            'status' => 5,
            'workflow_status' => 'REJECTED',
            'description' => $dto->description,
        ]);
    }
}
