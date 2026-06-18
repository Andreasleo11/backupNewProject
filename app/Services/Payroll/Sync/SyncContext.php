<?php

declare(strict_types=1);

namespace App\Services\Payroll\Sync;

use App\Services\Payroll\Progress\ProgressReporter;
use Carbon\CarbonImmutable;

/**
 * Immutable value object passed through the sync pipeline.
 * Each SyncPhase reads what it needs from here.
 */
final class SyncContext
{
    public function __construct(
        public readonly string          $companyArea,
        public readonly int             $year,
        public readonly CarbonImmutable $from,
        public readonly CarbonImmutable $to,
        public readonly ProgressReporter $progress,
    ) {}
}
