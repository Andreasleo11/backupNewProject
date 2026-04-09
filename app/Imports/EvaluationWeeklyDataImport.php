<?php

namespace App\Imports;

use App\Models\EvaluationDataWeekly;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Carbon\Carbon;

class EvaluationWeeklyDataImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $nik = trim($row['nik'] ?? $row['NIK'] ?? $row['employee_id'] ?? '');
        if (empty($nik)) {
            return null;
        }

        // Parse month/date
        $monthInput = $row['month'] ?? $row['Month'] ?? $row['periode'] ?? $row['Periode'] ?? null;
        $month = null;
        try {
            if ($monthInput) {
                if (is_numeric($monthInput)) {
                    $month = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($monthInput)->format('Y-m-d');
                } else {
                    try {
                        $month = Carbon::createFromFormat('d/m/Y', $monthInput)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $month = Carbon::parse($monthInput)->format('Y-m-d');
                    }
                }
            }
        } catch (\Exception $e) {
            // Fallback
        }

        // Resolve Department if not provided
        $dept = $row['dept'] ?? $row['Dept'] ?? $row['department'] ?? null;
        if (empty($dept)) {
            $employee = Employee::where('nik', $nik)->first();
            $dept = $employee?->dept_no;
        }

        // Upsert for weekly: NIK + Month combo
        $existing = EvaluationDataWeekly::where('NIK', $nik)
            ->where('Month', $month)
            ->first();

        $data = [
            'NIK'   => $nik,
            'dept'  => $dept,
            'Month' => $month,
            'Telat' => (int)($row['telat'] ?? $row['T'] ?? $row['terlambat'] ?? 0),
            'Alpha' => (int)($row['alpha'] ?? $row['A'] ?? 0),
            'Izin'  => (int)($row['izin'] ?? $row['I'] ?? 0),
            'Sakit' => (int)($row['sakit'] ?? $row['S'] ?? 0)
        ];

        if ($existing) {
            // Manual update or use updateOrCreate
            $existing->update($data);
            return null;
        }

        return new EvaluationDataWeekly($data);
    }
}
