<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\DTOs;

final class CreatePurchaseRequestDTO
{
    /** @param list<PurchaseRequestItemDTO> $items */
    public function __construct(
        public readonly int $requestedByUserId,
        public readonly string $fromDepartment,
        public readonly string $toDepartment, // normalized: PURCHASING/PERSONALIA/...
        public readonly string $branch,
        public readonly string $datePr,
        public readonly string $dateRequired,
        public readonly ?string $remark,
        public readonly ?string $supplier,
        public readonly ?string $pic,
        public readonly bool $isDraft,
        public readonly ?bool $isImport,
        public readonly array $items,
    ) {}
}
