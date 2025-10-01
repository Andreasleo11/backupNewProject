<?php

declare(strict_types=1);

namespace App\Domain\Expenses\UseCases;

use App\Domain\Expenses\Contracts\ExpenseReadRepository;
use App\Domain\Expenses\ValueObjects\Month;

/**
 * Application use case: list distinct PR approvers (autograph_5) for a given month.
 * Returns a plain array of strings, sorted as provided by the repository.
 */
final class ListPrSigners
{
    public function __construct(private readonly ExpenseReadRepository $repo) {}

    /** @return list<string> */
    public function execute(string $ym): array
    {
        return $this->repo->listPrSigners(new Month($ym));
    }
}
