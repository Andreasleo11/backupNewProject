<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\DTOs;

final class PurchaseRequestItemDTO
{
    public function __construct(
        public readonly string $itemName,
        public readonly float $quantity,
        public readonly string $purpose,
        public readonly float $price,
        public readonly string $uom,
        public readonly string $currency,
    ) {}
}
