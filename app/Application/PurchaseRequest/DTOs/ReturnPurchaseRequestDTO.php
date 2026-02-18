<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\DTOs;

final class ReturnPurchaseRequestDTO
{
    public function __construct(
        public readonly int $purchaseRequestId,
        public readonly int $actorUserId,
        public readonly string $reason,
    ) {}
}
