<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\Payroll\Contracts\JPayrollClientContract;
use App\Services\Payroll\PayrollSyncOrchestrator;
use App\Services\Payroll\Sync\DateRangeResolver;
use App\Services\Payroll\Sync\Phases\AnnualLeaveSyncPhase;
use App\Services\Payroll\Sync\Phases\AttendanceSyncPhase;
use App\Services\Payroll\Sync\Phases\EmployeeSyncPhase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Runs only the sync phases explicitly requested by the user.
 * Replaces the old SyncEmployeesJob which always ran all three phases.
 */
class PayrollSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param string   $companyArea
     * @param int      $year
     * @param string[] $phases       Subset of: 'employees', 'annual_leave', 'attendance'
     * @param ?string  $fromDate     ISO date string (for attendance)
     * @param ?string  $toDate       ISO date string (for attendance)
     * @param ?int     $importJobId
     */
    public function __construct(
        protected string  $companyArea,
        protected int     $year,
        protected array   $phases,
        protected ?string $fromDate    = null,
        protected ?string $toDate      = null,
        protected ?int    $importJobId = null,
    ) {}

    public function handle(
        JPayrollClientContract $client,
        DateRangeResolver      $resolver,
        EmployeeSyncPhase      $employeePhase,
        AnnualLeaveSyncPhase   $leavePhase,
        AttendanceSyncPhase    $attendancePhase,
    ): void {
        $importJob = $this->importJobId
            ? \App\Models\ImportJob::find($this->importJobId)
            : null;

        // Build only the requested phases in a fixed order
        $phaseMap = [
            'employees'    => $employeePhase,
            'annual_leave' => $leavePhase,
            'attendance'   => $attendancePhase,
        ];

        $selectedPhases = array_values(
            array_filter(
                $phaseMap,
                fn ($key) => in_array($key, $this->phases, true),
                ARRAY_FILTER_USE_KEY,
            ),
        );

        $tz    = config('payroll.timezone', 'Asia/Jakarta');
        $range = $resolver->resolve($this->fromDate, $this->toDate, $tz);

        $orchestrator = new PayrollSyncOrchestrator($selectedPhases);
        $result = $orchestrator->run(
            $this->companyArea,
            $this->year,
            $range['from'],
            $range['to'],
        );

        if ($importJob) {
            $importJob->update([
                'status'      => $result['success'] ? 'completed' : 'failed',
                'error'       => $result['success'] ? null : $result['message'],
                'finished_at' => now(),
            ]);
        }

        if (! $result['success']) {
            Log::error('PayrollSyncJob failed', ['message' => $result['message'], 'phases' => $this->phases]);
        } else {
            Log::info('PayrollSyncJob completed', ['message' => $result['message'], 'phases' => $this->phases]);
        }
    }
}
