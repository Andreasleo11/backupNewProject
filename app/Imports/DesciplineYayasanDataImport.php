<?php

namespace App\Imports;

use App\Models\EvaluationData;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;

class DesciplineYayasanDataImport implements ToModel, WithUpserts
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
            'kemampuan_kerja'  => $row[3] ?? 0,
            'kecerdasan_kerja'  => $row[4] ?? 0,
            'qualitas_kerja'  => $row[5] ?? 0,
            'disiplin_kerja'  => $row[6] ?? 0,
            'kepatuhan_kerja'  => $row[7] ?? 0,
            'lembur'  => $row[8] ?? 0,
            'efektifitas_kerja'  => $row[9] ?? 0,
            'relawan'  => $row[10] ?? 0,
            'integritas'  => $row[11] ?? 0,
        ]);
    }

    public function uniqueBy()
    {
        return ['NIK']; // Specify the column name(s) to check for duplicates
    }

}