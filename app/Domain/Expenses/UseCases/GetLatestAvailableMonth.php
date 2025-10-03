<?php

namespace App\Domain\Expenses\UseCases;

use App\Domain\Expenses\Contracts\ExpenseReadRepository;

final class GetLatestAvailableMonth
{
    public function __construct(private ExpenseReadRepository $repo) {}

    public function execute(?string $prSigner = null): ?string
    {
        return $this->repo->getLatestMonth($prSigner);
    }
}
