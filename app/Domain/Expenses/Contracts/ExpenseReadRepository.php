<?php

namespace App\Domain\Expenses\Contracts;

use App\Domain\Expenses\DTO\DepartmentTotal;
use App\Domain\Expenses\DTO\ExpenseLine;
use App\Domain\Expenses\ValueObjects\Month;

interface ExpenseReadRepository
{
    /** @return list<string> */
    public function listPrSigners(Month $month): array;

    /** @return list<DepartmentTotal> */
    public function getDepartmentTotals(Month $month, ?string $prSigner): array;

    /** @return array{items:list<ExpenseLine>, total:int, page:int, perPage:int} */
    public function getExpenseLinesByDepartment(
        int $deptId,
        Month $month,
        ?string $prSigner,
        string $sortBy,
        string $sortDir,
        int $page,
        int $perPage,
        ?string $search = null,
    ): array;

    /**
     * @return list<DepartmentTotal>
     */
    public function getMonthlyDepartmentTotals(
        Month $start,
        Month $end,
        ?string $prSigner
    ): array;

    /**
     * Returns the latest year-month (YYYY-MM) that has any expense data.
     * If $prSigner is set, PR lines are restricted to that signer; MB lines are unaffected.
     */
    public function getLatestMonth(?string $prSigner = null): ?string;

    /**
     * @return array<int, string> // list of 'YYYY-MM' sorted DESC (latest first)
     */
    public function listMonths(?string $prSigner = null, int $limit = 24): array;
}
