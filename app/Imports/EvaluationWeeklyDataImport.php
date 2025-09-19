<?php

namespace App\Imports;

use App\Models\EvaluationDataWeekly;
use Maatwebsite\Excel\Concerns\ToModel;

class EvaluationWeeklyDataImport implements ToModel
{
    public function model(array $row)
    {
        /**
         * @param array $row
         * @return \Illuminate\Database\Eloquent\Model|null
         */
        return new EvaluationDataWeekly([
            "NIK" => $row[0],
            "Month" => $row[1],
            "Telat" => $row[2] ?? 0,
            "Alpha" => $row[3] ?? 0,
            "Izin" => $row[4] ?? 0,
            "Sakit" => $row[5] ?? 0,
        ]);
    }
}
