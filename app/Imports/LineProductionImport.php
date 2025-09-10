<?php

namespace App\Imports;

use App\Models\sapLineProduction;
use Maatwebsite\Excel\Concerns\ToModel;

class LineProductionImport implements ToModel
{
    public function model(array $row)
    {
        /**
         * @param array $row
         * @return \Illuminate\Database\Eloquent\Model|null
         */
        return new sapLineProduction([
            "item_code" => $row[0],
            "line_production" => $row[1],
            "priority" => $row[2],
        ]);
    }
}
