<?php
// app/Contracts/ExpenseSource.php
namespace App\Contracts;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface ExpenseSource
{
    /** Return rows: department_id, expense (float), expense_date (date), source (string) */
    public function fetch(Carbon $start, Carbon $end): Collection;
}
