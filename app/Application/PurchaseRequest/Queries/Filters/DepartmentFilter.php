<?php

namespace App\Application\PurchaseRequest\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Filter by destination department (to_department column).
 */
final class DepartmentFilter implements PurchaseRequestFilter
{
    public function __construct(private readonly string $department) {}

    public function apply(Builder $query): void
    {
        $query->where('to_department', $this->department);
    }
}
