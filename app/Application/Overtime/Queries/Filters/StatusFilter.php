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
                // To be "Awaiting Review", the form must be fully signed (APPROVED) 
                // but the details must not have been reviewed yet (NULL).
                $query->where('status', 'APPROVED')
                      ->whereHas('details', fn($q) => $q->whereNull('status'));
            } else {
                $query->whereHas('details', function ($q) use ($status) {
                    $q->where('status', ucfirst(strtolower($status)));
                });
            }
        }
    }
}
