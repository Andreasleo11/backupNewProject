<?php

namespace App\Domain\Expenses\UseCases;

use App\Domain\Expenses\Contracts\ExpenseReadRepository;

final class GetRollingDepartmentTotals
{
    public function __construct(private ExpenseReadRepository $repo) {}

    public function execute(string $endYm, int $monthsBack, ?string $prSigner): array
    {
        $end = new \App\Domain\Expenses\ValueObjects\Month($endYm);
        $startYm = (new \DateTimeImmutable($endYm.'-01'))->modify('-'.($monthsBack - 1).' months')->format('Y-m');
        $start = new \App\Domain\Expenses\ValueObjects\Month($startYm);

        return $this->repo->getMonthlyDepartmentTotals($start, $end, $prSigner);
    }
}
