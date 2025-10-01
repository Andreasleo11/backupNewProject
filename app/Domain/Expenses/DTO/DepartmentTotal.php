<?php

namespace App\Domain\Expenses\DTO;

final readonly class DepartmentTotal
{
    public function __construct(
        public int $deptId,
        public string $deptName,
        public ?string $deptNo,
        public float $totalExpense,
    ) {}
}
