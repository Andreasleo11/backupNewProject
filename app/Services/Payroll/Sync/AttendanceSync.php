<?php

declare(strict_types=1);

namespace App\Services\Payroll\Sync;

use App\Repositories\AttendanceRepository;
use App\Services\Payroll\Dto\AttendanceDto;

/**
 * Persists raw daily attendance records fetched from JPayroll.
 * All weekly / monthly aggregations are computed at query time in the UI layer.
 */
final class AttendanceSync
{
    public function __construct(
        private readonly AttendanceRepository $repo,
    ) {}

    /** @param AttendanceDto[] $items */
    public function sync(array $items): int
    {
        // Filter out items without a nik (defensive guard)
        $items = array_filter($items, fn (AttendanceDto $dto) => $dto->nik !== '');

        return $this->repo->upsertDaily(array_values($items));
    }
}
