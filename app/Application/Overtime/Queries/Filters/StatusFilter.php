<?php

namespace App\Application\Overtime\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;

class StatusFilter implements OvertimeFilter
{
    public function __construct(private ?string $status) {}

    public function apply(Builder $query): void
    {
        if ($this->status) {
            $status = strtoupper($this->status);
            if ($status === 'PENDING') {
                $query->whereHas('details', fn($q) => $q->whereNull('status'));
            } else {
                $query->whereHas('details', function ($q) use ($status) {
                    $q->where('status', ucfirst(strtolower($status)));
                });
            }
        }
    }
}
