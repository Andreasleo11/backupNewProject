<?php

namespace App\Application\Overtime\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;

class SearchFilter implements OvertimeFilter
{
    public function __construct(private ?string $search) {}

    public function apply(Builder $query): void
    {
        if (! empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($qq) use ($s) {
                if (ctype_digit($s)) {
                    $qq->orWhere('id', (int) $s);
                }
                $qq->orWhere('branch', 'like', $s . '%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $s . '%'));
            });
        }
    }
}
