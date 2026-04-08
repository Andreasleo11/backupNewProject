<?php

namespace App\Application\Overtime\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class DateRangeFilter implements OvertimeFilter
{
    public function __construct(
        private ?string $startDate,
        private ?string $endDate
    ) {}

    public function apply(Builder $query): void
    {
        if ($this->startDate && $this->endDate) {
            $query->whereHas('details', function ($q) {
                $q->whereDate('start_date', '>=', $this->startDate)
                  ->whereDate('start_date', '<=', $this->endDate);
            });
        }
    }
}
