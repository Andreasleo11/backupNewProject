<?php

declare(strict_types=1);

namespace App\Services\Payroll\Sync\Phases;

use App\Services\Payroll\Contracts\JPayrollClientContract;
use App\Services\Payroll\Contracts\SyncPhase;
use App\Services\Payroll\Sync\EmployeeSync;
use App\Services\Payroll\Sync\SyncContext;

final class EmployeeSyncPhase implements SyncPhase
{
    public function __construct(
        private readonly JPayrollClientContract $client,
        private readonly EmployeeSync           $sync,
    ) {}

    public function execute(SyncContext $ctx): void
    {
        $employees = $this->client->getMasterEmployees($ctx->companyArea);
        $affected  = $this->sync->sync($employees);

        $ctx->progress->phase('employees', $affected, count($employees));
    }
}
