<?php
declare(strict_types=1);

namespace App\Services;

use App\Services\Payroll\Contracts\JPayrollClientContract;
use App\Services\Payroll\Progress\ProgressReporter;
use App\Services\Payroll\Sync\{EmployeeSync, AnnualLeaveSync, AttendanceWeeklySync};
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class JPayrollService
{
    public function __construct(
        private readonly JPayrollClientContract $client,
        private readonly EmployeeSync $employeeSync,
        private readonly AnnualLeaveSync $leaveSync,
        private readonly AttendanceWeeklySync $attendanceSync,
    ) {}

    public function syncEmployeesLeaveAndAttendanceFromApi(
        string $companyArea = '10000',
        ?int $year = null,
        CarbonImmutable|string|null $fromDate = null,
        CarbonImmutable|string|null $toDate = null
    ): array {
        $tz = config('payroll.timezone', 'Asia/Jakarta');

        $year ??= now($tz)->year;
        $from = $fromDate instanceof CarbonImmutable ? $fromDate : ($fromDate ? CarbonImmutable::parse($fromDate, $tz) : now($tz)->startOfMonth()->toImmutable());
        $to   = $toDate   instanceof CarbonImmutable ? $toDate   : ($toDate   ? CarbonImmutable::parse($toDate, $tz)   : now($tz)->subDay()->endOfDay()->toImmutable());

        if ($from->gt($to)) {
            return ['success'=>false, 'message'=>"Invalid range: {$from->toDateString()} > {$to->toDateString()}"];
        }

        $progress = new ProgressReporter($companyArea);

        try {
            // Phase 1: Employees
            $employees = $this->client->getMasterEmployees($companyArea);
            $affected  = $this->employeeSync->sync($employees);
            $progress->put('employees', $affected, count($employees));

            // Phase 2: Annual leave
            $leaves = $this->client->getAnnualLeave($companyArea, $year);
            $this->leaveSync->sync($leaves);
            $progress->put('annual_leave', count($leaves), count($leaves));

            // Phase 3: Attendance weekly (slice by weeks)
            $processed = 0;
            $cursor = $from->startOfWeek(\Carbon\CarbonInterface::MONDAY);
            $end    = $to->endOfDay();

            while ($cursor->lte($end)) {
                $rangeStart = $cursor;
                $rangeEnd   = $cursor->endOfWeek(\Carbon\CarbonInterface::SUNDAY);
                if ($rangeEnd->gt($end)) $rangeEnd = $end;

                $batch = $this->client->getAttendance($companyArea, $rangeStart, $rangeEnd, null);
                $processed += count($batch);
                $this->attendanceSync->sync($batch);

                $progress->put(
                    'attendance',
                    $processed,
                    null, // unknown total (unless you estimate)
                    $rangeStart->toDateString().' → '.$rangeEnd->toDateString()
                );

                $cursor = $cursor->addWeek();
            }

            return ['success'=>true, 'message'=>'Sync completed'];
        } catch (Throwable $e) {
            Log::error('Sync failed', [
                'companyArea'=>$companyArea,
                'year'=>$year,
                'from'=>$from->toDateString(),
                'to'=>$to->toDateString(),
                'error'=>$e->getMessage(),
            ]);
            return ['success'=>false, 'message'=>'Sync failed: '.$e->getMessage()];
        }
    }
}
