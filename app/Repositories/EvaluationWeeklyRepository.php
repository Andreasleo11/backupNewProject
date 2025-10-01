<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

final class EvaluationWeeklyRepository
{
    /**
     * Unique key: (NIK, Month)
     *
     * @param  array<array<string,mixed>>  $rows
     */
    public function upsertWeekly(array $rows): int
    {
        if (! $rows) {
            return 0;
        }

        return DB::transaction(function () use ($rows) {
            return DB::table('evaluation_data_weekly')->upsert(
                array_map(fn ($r) => $r + ['updated_at' => now(), 'created_at' => now()], $rows),
                ['NIK', 'Month'],
                ['dept', 'Alpha', 'Telat', 'Izin', 'Sakit', 'updated_at'],
            );
        });
    }
}
