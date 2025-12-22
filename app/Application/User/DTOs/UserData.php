<?php

namespace App\Application\User\DTOs;

class UserData
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password,
        /** @var string[] */
        public array $roles,
        public bool $active = true,
        public ?int $employeeId = null,
    ) {}
}
