<?php

declare(strict_types=1);

namespace App\Domain\Expenses\UseCases;

use App\Domain\Expenses\Contracts\ExpenseReadRepository;
use App\Domain\Expenses\ValueObjects\Month;

/**
 * Application use case: fetch paginated/sorted expense lines for a department in a month,
 * optionally filtered by PR approver (autograph_5).
 *
 * Returns a neutral array:
 * [
 *   'items'   => list<ExpenseLine>,
 *   'total'   => int,
 *   'page'    => int,
 *   'perPage' => int,
 * ]
 */
final class GetExpenseDetail
{
    public function __construct(private readonly ExpenseReadRepository $repo) {}

    /**
     * @return array{items: array<int,\App\Domain\Expenses\DTO\ExpenseLine>, total:int, page:int, perPage:int}
     */
    public function execute(
        int $deptId,
        string $ym,
        ?string $prSigner,
        string $sortBy = 'expense_date',
        string $sortDir = 'desc',
        int $page = 1,
        int $perPage = 25,
    ): array {
        // Basic guards (keep domain safe; infra can still re-validate)
        $page = max(1, $page);
        $perPage = max(1, min(1000, $perPage)); // hard cap to prevent abuse

        $month = new Month($ym);

        return $this->repo->getExpenseLinesByDepartment(
            deptId: $deptId,
            month: $month,
            prSigner: $prSigner,
            sortBy: $sortBy,
            sortDir: strtolower($sortDir) === 'asc' ? 'asc' : 'desc',
            page: $page,
            perPage: $perPage,
        );
    }
}
