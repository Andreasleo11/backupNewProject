<?php

namespace App\Imports;

use App\Models\SapDelsched;
use Maatwebsite\Excel\Concerns\ToModel;

class DelschedImport implements ToModel
{
    public function model(array $row)
    {
        /**
         * @param array $row
         * @return \Illuminate\Database\Eloquent\Model|null
         */
        return new SapDelsched([
            "item_code" => $row[0],
            "delivery_date" => $row[1],
            "delivery_qty" => $row[2],
            "so_number" => $row[3] ?? "",
        ]);
    }
}
