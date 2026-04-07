<?php

namespace App\Application\Overtime\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;

class DepartmentFilter implements OvertimeFilter
{
    public function __construct(private ?int $departmentId) {}

    public function apply(Builder $query): void
    {
        if ($this->departmentId) {
            $query->where('dept_id', $this->departmentId);
        }
    }
}
