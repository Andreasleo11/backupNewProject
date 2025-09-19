<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class EmployeeJabatanImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        // Skip header row
        foreach ($rows->skip(1) as $row) {
            $nik = str_pad($row[1], 5, "0", STR_PAD_LEFT); // Ensure 5-digit format
            $jabatan = $row[3];

            Employee::where("NIK", $nik)->update(["jabatan" => $jabatan]);
        }
    }
}
