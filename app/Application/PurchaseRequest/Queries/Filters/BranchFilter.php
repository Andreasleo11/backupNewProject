<?php

namespace App\Application\PurchaseRequest\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Filter by branch (e.g. JAKARTA, KARAWANG).
 */
final class BranchFilter implements PurchaseRequestFilter
{
    public function __construct(private readonly string $branch) {}

    public function apply(Builder $query): void
    {
        $query->where('branch', $this->branch);
    }
}
