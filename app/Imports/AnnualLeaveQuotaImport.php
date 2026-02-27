<?php

namespace App\Imports;

use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class AnnualLeaveQuotaImport implements ToModel, WithHeadingRow, WithStartRow
{
    /**
     * Define the starting row (ignores first 7 rows)
     */
    public function startRow(): int
    {
        return 8; // Start reading from row 8 (row numbers are 1-based)
    }

    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Find employee by Employee ID (NIK)
        $employee = Employee::where('nik', $row[1])->first();

        if ($employee) {
            $employee->jatah_cuti_tahun = $row[3];
            $employee->save();
        }

        return $employee;
    }
}
