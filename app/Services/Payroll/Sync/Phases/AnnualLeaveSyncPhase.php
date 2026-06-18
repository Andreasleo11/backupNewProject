<?php

declare(strict_types=1);

namespace App\Services\Payroll\Sync\Phases;

use App\Services\Payroll\Contracts\JPayrollClientContract;
use App\Services\Payroll\Contracts\SyncPhase;
use App\Services\Payroll\Sync\AnnualLeaveSync;
use App\Services\Payroll\Sync\SyncContext;

final class AnnualLeaveSyncPhase implements SyncPhase
{
    public function __construct(
        private readonly JPayrollClientContract $client,
        private readonly AnnualLeaveSync        $sync,
    ) {}

    public function execute(SyncContext $ctx): void
    {
        $leaves = $this->client->getAnnualLeave($ctx->companyArea, $ctx->year);
        $this->sync->sync($leaves);

        $ctx->progress->phase('annual_leave', count($leaves), count($leaves));
    }
}
