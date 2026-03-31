<?php

namespace App\Application\PurchaseRequest\DTOs;

class GetPurchaseRequestListDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?string $status = null,
        public readonly ?string $branch = null,
        public readonly int $perPage = 10,
        public readonly bool $wideView = false,
    ) {}
}
