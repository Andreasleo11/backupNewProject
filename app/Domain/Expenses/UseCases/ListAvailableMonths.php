<?php

namespace App\Domain\Expenses\UseCases;

use App\Domain\Expenses\Contracts\ExpenseReadRepository;
use Carbon\Carbon;

final class ListAvailableMonths
{
    public function __construct(private ExpenseReadRepository $repo) {}

    /**
     * @return array<int, array{value:string, label:string}>
     */
    public function execute(?string $prSigner = null, int $limit = 24): array
    {
        $months = $this->repo->listMonths($prSigner, $limit); // ['2025-04','2025-03',...]

        return array_map(function (string $ym) {
            $label = Carbon::parse($ym.'-01')->isoFormat('MMM YYYY');

            return ['value' => $ym, 'label' => $label];
        }, $months);

    }
}
