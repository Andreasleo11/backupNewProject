<?php

declare(strict_types=1);

namespace App\Services\Payroll;

use App\Services\Payroll\Contracts\SyncPhase;
use App\Services\Payroll\Progress\ProgressReporter;
use App\Services\Payroll\Sync\SyncContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Runs an ordered list of SyncPhases and reports overall progress.
 * Adding a new phase = register a new SyncPhase implementation; no edits here.
 */
final class PayrollSyncOrchestrator
{
    /** @param SyncPhase[] $phases */
    public function __construct(
        private readonly array $phases,
    ) {}

    public function run(
        string          $companyArea,
        int             $year,
        CarbonImmutable $from,
        CarbonImmutable $to,
    ): array {
        $progress = new ProgressReporter($companyArea);
        $progress->start();

        $ctx = new SyncContext($companyArea, $year, $from, $to, $progress);

        try {
            foreach ($this->phases as $phase) {
                $phase->execute($ctx);
            }

            $progress->done();

            return ['success' => true, 'message' => 'Sync completed'];
        } catch (Throwable $e) {
            $progress->error($e->getMessage());
            Log::error('Sync failed', ['error' => $e->getMessage()]);

            return ['success' => false, 'message' => 'Sync failed: ' . $e->getMessage()];
        }
    }
}
