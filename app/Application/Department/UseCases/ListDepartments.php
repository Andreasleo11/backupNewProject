<?php

namespace App\Application\Department\UseCases;

use App\Application\Department\DTOs\DepartmentFilter;
use App\Domain\Department\Repositories\DepartmentRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ListDepartments
{
    public function __construct(
        private DepartmentRepository $departments,
    ) {}

    /**
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function execute(DepartmentFilter $filter): LengthAwarePaginator
    {
        return $this->departments->paginate(
            search: $filter->search,
            branch: $filter->branch,
            onlyActive: $filter->onlyActive,
            perPage: $filter->perPage,
        );
    }
}