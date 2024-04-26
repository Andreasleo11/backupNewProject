<?php

namespace App\Imports;

use App\Models\EvaluationData;
use Maatwebsite\Excel\Concerns\ToModel;

class EvaluationDataImport implements ToModel
{

        public function model(array $row)
        {
                /**
            * @param array $row
            * @return \Illuminate\Database\Eloquent\Model|null
            */
            return new EvaluationData([
                'NIK'  => $row[0],
                'Month' => $row[1],
                'Alpha'  => $row[2] ?? 0,
                'Telat'  => $row[3] ?? 0,
                'Izin'  => $row[4] ?? 0,
                'Sakit' => $row[5] ??0,
            ]);
        
        }
    }
