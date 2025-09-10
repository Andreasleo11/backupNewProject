<?php
declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

final class EmployeeRepository
{
    /**
     * Upsert by NIK.
     * @param array<array<string,mixed>> $rows
     */
    public function upsert(array $rows): int
    {
        if (!$rows) return 0;

        return DB::transaction(function () use ($rows) {
            return DB::table('employees')->upsert(
                array_map(fn($r) => $r + ['updated_at'=>now(), 'created_at'=>now()], $rows),
                ['NIK'],
                ['Nama','Gender','Dept','start_date','end_date','Grade','employee_status','Branch','status','organization_structure','updated_at']
            );
        });
    }

    /**
     * Update leave balances (may vary by schema).
     * @param array<string,int> $nikToRemain
     */
    public function updateLeaveBalances(array $nikToRemain): void
    {
        DB::transaction(function () use ($nikToRemain) {
            foreach (array_chunk($nikToRemain, 500, true) as $chunk) {
                foreach ($chunk as $nik => $remain) {
                    DB::table('employees')->where('NIK', $nik)->update([
                        'jatah_cuti_tahun' => $remain,
                        'updated_at'       => now(),
                    ]);
                }
            }
        });
    }

    public function getDeptForNik(string $nik): ?string
    {
        return DB::table('employees')->where('NIK',$nik)->value('Dept');
    }
}
