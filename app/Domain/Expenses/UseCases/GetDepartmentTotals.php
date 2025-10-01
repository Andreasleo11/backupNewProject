<?php

namespace App\Domain\Expenses\UseCases;

use App\Domain\Expenses\Contracts\ExpenseReadRepository;
use App\Domain\Expenses\ValueObjects\Month;

final class GetDepartmentTotals
{
    public function __construct(private ExpenseReadRepository $repo) {}

    public function execute(string $ym, ?string $prSigner): array
    {
        return $this->repo->getDepartmentTotals(new Month($ym), $prSigner);
    }
}
