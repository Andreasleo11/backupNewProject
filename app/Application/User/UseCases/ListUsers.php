<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\UserFilter;
use App\Domain\User\Repositories\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ListUsers
{
    public function __construct(
        private UserRepository $users
    ) {}

    public function execute(UserFilter $filter): LengthAwarePaginator
    {
        return $this->users->paginate(
            perPage: $filter->perPage,
            search: $filter->search,
            onlyActive: $filter->onlyActive,
        );
    }
}
