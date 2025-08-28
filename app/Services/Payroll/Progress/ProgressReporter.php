<?php
declare(strict_types=1);

namespace App\Services\Payroll\Progress;

use Illuminate\Support\Facades\Cache;

final class ProgressReporter
{
    public function __construct(private readonly string $companyArea) {}

    public function put(string $phase, int $processed, ?int $total, ?string $range = null): void
    {
        Cache::put("sync_progress_{$this->companyArea}", [
            'phase'     => $phase,           // employees|annual_leave|attendance
            'processed' => $processed,
            'total'     => $total,
            'percent'   => $total ? (int) floor($processed / max(1,$total) * 100) : null,
            'last_range'=> $range,
            'updated'   => now()->toDateTimeString(),
        ], now()->addMinutes(30));
    }
}
