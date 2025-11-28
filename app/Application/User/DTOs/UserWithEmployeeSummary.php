<?php

namespace App\Application\User\DTOs;

class UserWithEmployeeSummary
{
    /**
     * @param string[] roles
     */

    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public bool $active,
        public array $roles,
        public ?int $employeeId,
        public ?string $employeeNik,
        public ?string $employeeName, 
        public ?string $employeeBranch, 
        public ?string $employeeDeptCode, 
    ) {}
}