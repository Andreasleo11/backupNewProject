<?php
namespace App\Domain\Department\Repositories;

use App\Domain\Department\Entities\Department;
use Illuminate\Pagination\LengthAwarePaginator;

interface DepartmentRepository 
{
    public function paginate(
        ?string $search, 
        ?string $branch, 
        ?bool $onlyActive, 
        int $perPage = 10, 
    ): LengthAwarePaginator;

    public function findById(int $id): ?Department;

    public function create(Department $department): Department;

    public function update(Department $department): Department;

    public function toggleActive(int $id): void;
}