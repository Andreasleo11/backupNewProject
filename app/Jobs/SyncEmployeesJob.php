<?php

namespace App\Jobs;

use App\Services\JPayrollService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncEmployeesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $companyArea;

    protected int $year;

    /**
     * Create a new job instance.
     */
    public function __construct(string $companyArea, int $year)
    {
        $this->companyArea = $companyArea;
        $this->year = $year ?? now()->year;
    }

    /**
     * Execute the job.
     */
    public function handle(JPayrollService $service): void
    {
        $result = $service->syncEmployeesLeaveAndAttendanceFromApi($this->companyArea, $this->year);

        if (! $result['success']) {
            Log::error('Sync failed in job: '.$result['message']);
        } else {
            Log::info('Sync completed in job: '.$result['message']);
        }
    }
}
