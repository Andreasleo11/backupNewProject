<?php

namespace App\Application\Overtime\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;

class PushStatusFilter implements OvertimeFilter
{
    public function __construct(private ?string $isPush) {}

    public function apply(Builder $query): void
    {
        if ($this->isPush === '0' || $this->isPush === '1') {
            $query->where('is_push', (int) $this->isPush);
        }
    }
}
