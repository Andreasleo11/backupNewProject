<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

final class EmployeeRepository
{
    /**
     * Upsert by NIK.
     *
     * @param array<array<string,mixed>> $rows
     */
    public function upsert(array $rows): int
    {
        if (! $rows) {
            return 0;
        }

        return DB::transaction(function () use ($rows) {
            return DB::table('employees')->upsert(
                array_map(fn ($r) => $r + ['updated_at' => now(), 'created_at' => now()], $rows),
                ['nik'],
                [
                    'name',
                    'gender',
                    'dept_code',
                    'start_date',
                    'end_date',
                    'grade_code',
                    'employment_type',
                    'branch',
                    'employment_scheme',
                    'organization_structure',
                    'updated_at',
                ],
            );
        });
    }

    /**
     * Get all employees indexed by NIK for comparison.
     *
     * @return array<string, object>
     */
    public function getAllForDiff(): array
    {
        return DB::table('employees')
            ->get()
            ->keyBy('nik')
            ->toArray();
    }

    /**
     * Update leave balances (may vary by schema).
     *
     * @param array<string,int> $nikToRemain
     */
    public function updateLeaveBalances(array $nikToRemain): void
    {
        DB::transaction(function () use ($nikToRemain) {
            foreach (array_chunk($nikToRemain, 500, true) as $chunk) {
                foreach ($chunk as $nik => $remain) {
                    DB::table('employees')
                        ->where('nik', $nik)
                        ->update([
                            'jatah_cuti_tahun' => $remain,
                            'updated_at' => now(),
                        ]);
                }
            }
        });
    }

    public function getDeptForNik(string $nik): ?string
    {
        return DB::table('employees')->where('nik', $nik)->value('dept_code');
    }
}
