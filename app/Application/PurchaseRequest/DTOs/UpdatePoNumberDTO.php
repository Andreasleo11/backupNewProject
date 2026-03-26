<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\DTOs;

final class UpdatePoNumberDTO
{
    public function __construct(
        public readonly int $purchaseRequestId,
        public readonly ?string $poNumber,
        public readonly int $updatedByUserId,
    ) {}
}
