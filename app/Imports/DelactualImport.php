<?php

namespace App\Imports;

use App\Models\SapDelactual;
use Maatwebsite\Excel\Concerns\ToModel;

class DelactualImport implements ToModel
{
    public function model(array $row)
    {
        /**
         * @param array $row
         * @return \Illuminate\Database\Eloquent\Model|null
         */
        return new SapDelactual([
            "item_no" => $row[0],
            "delivery_date" => $row[1],
            "item_name" => $row[2],
            "quantity" => $row[3],
            "so_num" => $row[4],
        ]);
    }
}
