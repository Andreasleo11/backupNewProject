<?php

declare(strict_types=1);

namespace App\Services\Payroll\Sync;

use Carbon\CarbonImmutable;

/**
 * Resolves an optional from/to pair into a concrete date range.
 * Defaults: from = start of current month, to = yesterday end-of-day.
 */
final class DateRangeResolver
{
    public function resolve(
        CarbonImmutable|string|null $from,
        CarbonImmutable|string|null $to,
        string $tz,
    ): array {
        $f = $from instanceof CarbonImmutable
            ? $from
            : ($from
                ? CarbonImmutable::parse($from, $tz)
                : now($tz)->startOfMonth()->toImmutable());

        $t = $to instanceof CarbonImmutable
            ? $to
            : ($to
                ? CarbonImmutable::parse($to, $tz)
                : now($tz)->subDay()->endOfDay()->toImmutable());

        return ['from' => $f, 'to' => $t];
    }
}
