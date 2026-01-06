<?php

namespace App\Application\PurchaseRequest\DTOs;

class ManualRejectPurchaseRequestDTO
{
    public function __construct(
        public readonly int $purchaseRequestId,
        public readonly string $description,
    ) {}
}
