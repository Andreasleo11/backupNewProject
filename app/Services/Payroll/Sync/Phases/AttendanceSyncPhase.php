<?php

declare(strict_types=1);

namespace App\Services\Payroll\Sync\Phases;

use App\Services\Payroll\Contracts\JPayrollClientContract;
use App\Services\Payroll\Contracts\SyncPhase;
use App\Services\Payroll\Sync\AttendanceSync;
use App\Services\Payroll\Sync\SyncContext;
use Carbon\CarbonInterface;

/**
 * Phase 3: slices the requested date range into weekly windows and
 * fetches + persists attendance records for each window.
 * Weekly batching keeps API payloads small and allows partial progress reporting.
 */
final class AttendanceSyncPhase implements SyncPhase
{
    public function __construct(
        private readonly JPayrollClientContract $client,
        private readonly AttendanceSync         $sync,
    ) {}

    public function execute(SyncContext $ctx): void
    {
        $cursor = $ctx->from->startOfWeek(CarbonInterface::MONDAY);

        while ($cursor->lte($ctx->to)) {
            $rangeEnd = $cursor
                ->endOfWeek(CarbonInterface::SUNDAY)
                ->min($ctx->to->endOfDay());

            $batch = $this->client->getAttendance(
                $ctx->companyArea,
                $cursor,
                $rangeEnd,
                null,
            );

            $this->sync->sync($batch);

            $ctx->progress->phase(
                'attendance',
                count($batch),
                null,
                "{$cursor->toDateString()} → {$rangeEnd->toDateString()}",
            );

            $cursor = $cursor->addWeek();
        }
    }
}
