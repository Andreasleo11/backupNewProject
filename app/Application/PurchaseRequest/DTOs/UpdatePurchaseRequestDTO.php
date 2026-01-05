<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\DTOs;

final class UpdatePurchaseRequestDTO
{
    public function __construct(
        public readonly int $purchaseRequestId,
        public readonly int $updatedByUserId,
        public readonly string $toDepartment,
        public readonly string $datePr,
        public readonly string $dateRequired,
        public readonly ?string $remark,
        public readonly ?string $supplier,
        public readonly ?string $pic,
        public readonly array $items, // Array of PurchaseRequestItemDTO
        public readonly ?bool $isImport,
    ) {}
}
