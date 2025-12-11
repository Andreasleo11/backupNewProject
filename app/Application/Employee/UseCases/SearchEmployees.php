<?php

namespace App\Application\Employee\UseCases;

use App\Application\Employee\DTOs\EmployeeSummary;
use App\Domain\Employee\Repositories\EmployeeRepository;

class SearchEmployees
{
    public function __construct(
        private EmployeeRepository $employees
    ) {}

    /**
     * @return EmployeeSummary[]
     */
    public function execute(string $term, int $limit = 10): array
    {
        $term = trim($term);

        if ($term === '') {
            return [];
        }

        $results = $this->employees->search($term, $limit);

        return array_map(function ($employee) {
            return new EmployeeSummary(
                id: $employee->id(),
                nik: $employee->nik(),
                name: $employee->name(),
                branch: $employee->branch(),
                deptCode: $employee->deptCode(),
            );
        }, $results);
    }
}
