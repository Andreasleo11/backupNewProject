<?php

namespace App\Application\PurchaseRequest\DTOs;

class AddSignatureDTO
{
    public function __construct(
        public readonly int $purchaseRequestId,
        public readonly int $signedByUserId,
        public readonly int $section,
        public readonly string $imagePath,
    ) {}
}
