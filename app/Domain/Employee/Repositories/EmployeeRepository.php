<?php

namespace App\Domain\Employee\Repositories;

use App\Domain\Employee\Entities\Employee;

interface EmployeeRepository
{
    public function findById(int $id): ?Employee;

    /**
     * Simple search for UI: NIK or name matches.
     *
     * @return Employee[]
     */
    public function search(string $term, int $limit = 10): array;

    /**
     * @param int[] $ids
     * @return Employee[]
     */
    public function findByIds(array $ids): array;
}
