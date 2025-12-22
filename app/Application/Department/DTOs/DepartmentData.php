<?php
namespace App\Application\Department\DTOs;

class DepartmentData 
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $deptNo,
        public readonly string $name,
        public readonly string $code,
        public readonly string $branch,
        public readonly bool $isOffice,
        public readonly bool $isActive,
    ){}
}