<?php

// app/Expenses/Sources/MonthlyBudgetSource.php
namespace App\Domain\Expenses\Sources;

use App\Contracts\ExpenseSource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MonthlyBudgetSource implements ExpenseSource
{
    public function fetch(Carbon $start, Carbon $end): Collection
    {
        return DB::table("monthly_budget_summary_reports")
            ->whereBetween("report_date", [$start, $end])
            ->selectRaw(
                "
                department_id,
                expense,
                CAST(report_date AS DATE) as expense_date,
                'monthly_budget' as source
            ",
            )
            ->get();
    }
}
