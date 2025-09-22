<?php

// app/Expenses/Sources/PurchaseRequestSource.php
namespace App\Domain\Expenses\Sources;

use App\Contracts\ExpenseSource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PurchaseRequestSource implements ExpenseSource
{
    public function fetch(Carbon $start, Carbon $end): Collection
    {
        return DB::table("purchase_requests")
            ->whereNull("deleted_at")
            ->whereBetween("request_date", [$start, $end])
            ->selectRaw(
                "
                department_id,
                amount as expense,
                CAST(request_date AS DATE) as expense_date,
                'purchase_request' as source
            ",
            )
            ->get();
    }
}
