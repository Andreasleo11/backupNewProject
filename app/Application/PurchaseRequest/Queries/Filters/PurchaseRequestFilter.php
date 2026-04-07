<?php

namespace App\Application\PurchaseRequest\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Contract for composable Purchase Request query filters.
 * Each filter encapsulates a single, focused filtering concern.
 */
interface PurchaseRequestFilter
{
    public function apply(Builder $query): void;
}
