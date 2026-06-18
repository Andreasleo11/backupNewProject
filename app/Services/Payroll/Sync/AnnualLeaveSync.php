<?php

declare(strict_types=1);

namespace App\Services\Payroll\Sync;

use App\Services\Payroll\Sync\EmployeeWriteRepository;
use App\Services\Payroll\Dto\AnnualLeaveDto;

final class AnnualLeaveSync
{
    public function __construct(private readonly EmployeeWriteRepository $repo) {}

    /** @param AnnualLeaveDto[] $items */
    public function sync(array $items): void
    {
        $map = [];
        foreach ($items as $it) {
            if ($it->remain !== null) {
                $map[$it->nik] = $it->remain;
            }
        }
        if ($map) {
            $this->repo->updateLeaveBalances($map);
        }
    }
}
