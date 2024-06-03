<?php

namespace App\Imports;

use App\Models\EvaluationData;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;

class DesciplineDataImport implements ToModel, WithUpserts
{

    public function model(array $row)
    {
        /**
         * @param array $row
         * @return \Illuminate\Database\Eloquent\Model|null
         */
        return new EvaluationData([
            'NIK' => $row[1],
            'Month' => $row[2],
            'kerajinan_kerja'  => $row[3] ?? 0,
            'kerapian_pakaian'  => $row[4] ?? 0,
            'kerapian_rambut'  => $row[5] ?? 0,
            'kerapian_sepatu'  => $row[6] ?? 0,
            'prestasi'  => $row[7] ?? 0,
            'loyalitas'  => $row[8] ?? 0,
        ]);
    }

    public function uniqueBy()
    {
        return ['NIK']; // Specify the column name(s) to check for duplicates
    }

}