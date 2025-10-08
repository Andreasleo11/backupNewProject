<?php

namespace App\Domain\Expenses\DTO;

final readonly class ExpenseLine
{
    public function __construct(
        public \DateTimeImmutable $expenseDate,
        public string $source,              // 'purchase_request' | 'monthly_budget'
        public ?string $autograph5,         // null for monthly_budget
        public int $docId,
        public string $docNum,
        public string $itemName,
        public string $uom,
        public float $quantity,
        public float $unitPrice,
        public float $lineTotal,
    ) {}
}
