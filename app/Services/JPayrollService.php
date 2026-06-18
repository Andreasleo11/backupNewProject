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

    public function previewSync(
        string $companyArea = '10000',
        ?int $year = null,
    ): array {
        $tz   = config('payroll.timezone', 'Asia/Jakarta');
        $year ??= now($tz)->year;

        try {
            $employees = $this->client->getMasterEmployees($companyArea);
            $preview   = $this->employeeSync->preview($employees);

            return array_merge(['success' => true], $preview);
        } catch (Throwable $e) {
            Log::error('Preview failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Preview failed: ' . $e->getMessage()];
        }
    }
}
