<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Services\Payroll\Dto\AttendanceDto;
use Illuminate\Support\Facades\DB;

final class AttendanceRepository
{
    /**
     * Upsert raw daily attendance rows.
     * Unique key: (nik, shift_date) — one row per employee per calendar day.
     *
     * @param  AttendanceDto[]  $items
     */
    public function upsertDaily(array $items): int
    {
        if (! $items) {
            return 0;
        }

        $now = now();

        $rows = array_map(fn (AttendanceDto $dto) => [
            'nik'        => $dto->nik,
            'shift_date' => $dto->shiftDate->toDateString(),
            'alpha'      => $dto->alpha,
            'telat'      => $dto->telat,
            'izin'       => $dto->izin,
            'sakit'      => $dto->sakit,
            'synced_at'  => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ], $items);

        return DB::transaction(function () use ($rows) {
            return DB::table('attendance_records')->upsert(
                $rows,
                ['nik', 'shift_date'],
                ['alpha', 'telat', 'izin', 'sakit', 'synced_at', 'updated_at'],
            );
        });
    }
}
