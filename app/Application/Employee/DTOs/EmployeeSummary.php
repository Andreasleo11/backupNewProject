<?php

namespace App\Application\Employee\DTOs;

class EmployeeSummary
{
    public function __construct(
        public int $id,
        public string $nik,
        public string $name,
        public ?string $branch,
        public ?string $deptCode,
    ) {}
}
