<?php

namespace App\Application\Overtime\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Contract for composable Overtime query filters.
 */
interface OvertimeFilter
{
    public function apply(Builder $query): void;
}
