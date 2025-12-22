<?php

namespace App\Application\User\DTOs;

class UserFilter
{
    public function __construct(
        public ?string $search = null,
        public ?bool $onlyActive = null,
        public int $perPage = 20,
    ) {}
}
