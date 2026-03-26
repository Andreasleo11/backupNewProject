<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\DTOs;

final class CancelPurchaseRequestDTO
{
    public function __construct(
        public readonly int $purchaseRequestId,
        public readonly int $cancelledByUserId,
        public readonly ?string $reason,
    ) {}
}
