<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Payroll\Contracts\JPayrollClientContract;
use App\Services\Payroll\PayrollSyncOrchestrator;
use App\Services\Payroll\Sync\DateRangeResolver;
use App\Services\Payroll\Sync\EmployeeSync;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Throwable;

final class JPayrollService
{
    public function __construct(
        private readonly JPayrollClientContract  $client,
        private readonly PayrollSyncOrchestrator $orchestrator,
        private readonly DateRangeResolver       $dateRangeResolver,
        private readonly EmployeeSync            $employeeSync,
        private readonly \App\Services\Payroll\Sync\AnnualLeaveSync $annualLeaveSync,
        private readonly \App\Services\Payroll\Sync\AttendanceSync $attendanceSync,
    ) {}

    public function syncEmployeesLeaveAndAttendanceFromApi(
        string $companyArea = '10000',
        ?int $year = null,
        CarbonImmutable|string|null $fromDate = null,
        CarbonImmutable|string|null $toDate = null,
    ): array {
        $tz   = config('payroll.timezone', 'Asia/Jakarta');
        $year ??= now($tz)->year;

        $range = $this->dateRangeResolver->resolve($fromDate, $toDate, $tz);

        if ($range['from']->gt($range['to'])) {
            return [
                'success' => false,
                'message' => "Invalid range: {$range['from']->toDateString()} > {$range['to']->toDateString()}",
            ];
        }

        return $this->orchestrator->run(
            $companyArea,
            $year,
            $range['from'],
            $range['to'],
        );
    }

    /**
     * Return a lightweight preview of what each selected phase will do.
     *
     * @param  string[]  $phases  Subset of: 'employees', 'annual_leave', 'attendance'
     * @param  string|null  $fromDate  ISO date (for attendance)
     * @param  string|null  $toDate    ISO date (for attendance)
     */
    public function previewSync(
        string $companyArea = '10000',
        ?int $year = null,
        array $phases = ['employees'],
        ?string $fromDate = null,
        ?string $toDate = null,
    ): array {
        $tz   = config('payroll.timezone', 'Asia/Jakarta');
        $year ??= now($tz)->year;

        try {
            $preview = ['phases' => $phases, 'parameters' => []];

            // --- Employee phase preview ---
            if (in_array('employees', $phases, true)) {
                $employees          = $this->client->getMasterEmployees($companyArea);
                $preview['employees'] = $this->employeeSync->preview($employees);
            }

            // --- Annual leave phase preview ---
            if (in_array('annual_leave', $phases, true)) {
                $leaves = $this->client->getAnnualLeave($companyArea, $year);
                $preview['annual_leave'] = $this->annualLeaveSync->preview($leaves);
            }

            // --- Attendance phase preview ---
            if (in_array('attendance', $phases, true)) {
                $range = $this->dateRangeResolver->resolve($fromDate, $toDate, $tz);
                $attendances = $this->client->getAttendance($companyArea, $range['from'], $range['to']);
                $preview['attendance'] = $this->attendanceSync->preview($attendances);
                
                $preview['parameters']['date_range'] = [
                    'requested_from' => $fromDate,
                    'requested_to'   => $toDate,
                    'resolved_from'  => $range['from']->format('Y-m-d'),
                    'resolved_to'    => $range['to']->format('Y-m-d'),
                ];
            }

            return array_merge(['success' => true], $preview);
        } catch (Throwable $e) {
            Log::error('Preview failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Preview failed: ' . $e->getMessage()];
        }
    }
}
