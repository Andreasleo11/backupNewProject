<?php

namespace App\Domain\Expenses;

use App\Domain\Expenses\Queries\UnifiedExpensesQuery;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ExpenseRepository
{
    /** Return [start, end] Carbon objects for a given "YYYY-MM" */
    private function monthRange(string $ym): array
    {
        $start = Carbon::parse($ym . "-01")->startOfMonth();
        $end = Carbon::parse($ym . "-01")->endOfMonth();
        return [$start, $end];
    }

    /** Totals per department for the month */
    public function totalsPerDepartmentForMonth(string $ym): Collection
    {
        [$start, $end] = $this->monthRange($ym);
        return UnifiedExpensesQuery::totalsPerDepartment($start, $end)->get();
    }

    /** Drilldown lines for a department for the month */
    public function detailByDepartmentForMonth(int $deptId, string $ym): Collection
    {
        [$start, $end] = $this->monthRange($ym);
        return UnifiedExpensesQuery::detailByDepartment($deptId, $start, $end)->get();
    }

    /** return a Builder so child component can filter/sort/paginate */
    public function detailQueryForMonth(int $deptId, string $ym): Builder
    {
        [$start, $end] = $this->monthRange($ym);
        return UnifiedExpensesQuery::detailByDepartment($deptId, $start, $end);
    }
}
