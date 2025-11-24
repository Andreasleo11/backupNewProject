<?php

namespace App\Domain\User\Repositories;

use App\Domain\User\Entities\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepository
{
    public function findById(int $id): ?User;
    public function findByEmail(string $email): ?User;
    public function findByEmployeeId(int $employeeId): ?User;

    /** @return \Illuminate\Pagination\LengthAwarePaginator */
    public function paginate(
        int $perPage,
        ?string $search = null,
        ?bool $onlyActive = null
    ): LengthAwarePaginator;

    public function create(User $user, string $plainPassword): User;
    public function update(User $user): User;
    public function delete(int $id): void;

    public function setRoles(User $user, array $roleNames): void;
    public function changePassword(int $userId, string $plainPassword): void;
}
