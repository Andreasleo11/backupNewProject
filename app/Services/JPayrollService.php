<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Payroll\Contracts\JPayrollClientContract;
use App\Services\Payroll\Progress\ProgressReporter;
use App\Services\Payroll\Sync\AnnualLeaveSync;
use App\Services\Payroll\Sync\AttendanceWeeklySync;
use App\Services\Payroll\Sync\EmployeeSync;
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
        CarbonImmutable|string|null $toDate = null,
    ): array {
        $tz = config('payroll.timezone', 'Asia/Jakarta');
        $year ??= now($tz)->year;

        $range = $this->resolveDateRange($fromDate, $toDate, $tz);

        if ($range['from']->gt($range['to'])) {
            return [
                'success' => false,
                'message' => "Invalid range: {$range['from']->toDateString()} > {$range['to']->toDateString()}",
            ];
        }

        $progress = new ProgressReporter($companyArea);
        $progress->start();

        try {
            // Phase 1: Employees
            $employees = $this->client->getMasterEmployees($companyArea);
            $affected = $this->employeeSync->sync($employees);
            $progress->phase('employees', $affected, count($employees));

            // Phase 2: Annual leave
            $leaves = $this->client->getAnnualLeave($companyArea, $year);
            $this->leaveSync->sync($leaves);
            $progress->phase('annual_leave', count($leaves), count($leaves));

            // Phase 3: Attendance weekly (slice by weeks)
            $this->syncAttendanceBatches($companyArea, $range['from'], $range['to'], $progress);

            $progress->done();

            return ['success' => true, 'message' => 'Sync completed'];
        } catch (Throwable $e) {
            $progress->error($e->getMessage());
            Log::error('Sync failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Sync failed: ' . $e->getMessage()];
        }
    }

    public function previewSync(
        string $companyArea = '10000',
        ?int $year = null,
    ): array {
        $tz = config('payroll.timezone', 'Asia/Jakarta');
        $year ??= now($tz)->year;

        try {
            $employees = $this->client->getMasterEmployees($companyArea);
            $preview = $this->employeeSync->preview($employees);

            return array_merge(['success' => true], $preview);
        } catch (Throwable $e) {
            Log::error('Preview failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Preview failed: ' . $e->getMessage()];
        }
    }

    private function resolveDateRange($from, $to, $tz): array
    {
        $f = $from instanceof CarbonImmutable ? $from : ($from ? CarbonImmutable::parse($from, $tz) : now($tz)->startOfMonth()->toImmutable());
        $t = $to instanceof CarbonImmutable ? $to : ($to ? CarbonImmutable::parse($to, $tz) : now($tz)->subDay()->endOfDay()->toImmutable());

        return ['from' => $f, 'to' => $t];
    }

    private function syncAttendanceBatches($area, $from, $to, $progress): void
    {
        $cursor = $from->startOfWeek(\Carbon\CarbonInterface::MONDAY);
        while ($cursor->lte($to)) {
            $rangeEnd = $cursor->endOfWeek(\Carbon\CarbonInterface::SUNDAY)->min($to->endOfDay());
            $batch = $this->client->getAttendance($area, $cursor, $rangeEnd, null);
            $this->attendanceSync->sync($batch);

            $progress->phase('attendance', count($batch), null, "{$cursor->toDateString()} → {$rangeEnd->toDateString()}");
            $cursor = $cursor->addWeek();
        }
    }
}
