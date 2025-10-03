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
}
