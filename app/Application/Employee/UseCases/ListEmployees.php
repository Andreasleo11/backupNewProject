<?php

namespace App\Application\Employee\UseCases;

use App\Application\Employee\DTOs\EmployeeFilter;
use App\Domain\Employee\Repositories\EmployeeRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ListEmployees
{
    public function __construct(
        private EmployeeRepository $employees,
    ){}

    public function execute(EmployeeFilter $filter): LengthAwarePaginator
    {
        return $this->employees->paginate(
            search: $filter->search,
            perPage: $filter->perPage,
        );
    }
}