<?php

namespace App\Application\Employee\DTOs;

class EmployeeFilter
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?int $perPage = 10,
        public ?string $sortBy = null,
        public string $sortDirection = 'asc',
        public readonly ?string $branch = null,
        public readonly ?string $deptCode = null,
        public readonly ?string $employmentType = null,
    ) {}
}
