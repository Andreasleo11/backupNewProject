<?php

namespace App\Application\Overtime\Queries\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class HideSignedFilter implements OvertimeFilter
{
    public function __construct(private bool $hideSigned) {}

    public function apply(Builder $query): void
    {
        if ($this->hideSigned && ! Auth::user()->hasRole('super-admin')) {
            $query->where(function ($q) {
                $q->where('status', '!=', 'IN_REVIEW')
                    ->orWhereDoesntHave('approvalRequest.steps', function ($stepQuery) {
                        $stepQuery->where('acted_by', Auth::id());
                    });
            });
        }
    }
}
