<?php
namespace App\Application\Department\DTOs;

class DepartmentFilter
{
    public function __construct(
        public readonly ?string $search = null,
        public readonly ?string $branch = null,
        public readonly ?bool $onlyActive = null,
        public readonly ?int $perPage = 10,
    ){}
}