<?php

namespace App\Domain\Expenses\UseCases;

final class GetDepartmentMonthlyTotals
{
    public function __construct(private \App\Domain\Expenses\Contracts\ExpenseReadRepository $repo) {}

    public function execute(string $startYm, string $endYm, ?string $prSigner): array
    {
        $start = new \App\Domain\Expenses\ValueObjects\Month($startYm);
        $end = new \App\Domain\Expenses\ValueObjects\Month($endYm);

        return $this->repo->getMonthlyDepartmentTotals($start, $end, $prSigner);
    }
}
