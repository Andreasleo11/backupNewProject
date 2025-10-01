<?php

namespace App\Imports;

use App\Models\SapBomWip;
use Maatwebsite\Excel\Concerns\ToModel;

class BomWipImport implements ToModel
{
    public function model(array $row)
    {
        /**
         * @param  array  $row
         * @return \Illuminate\Database\Eloquent\Model|null
         */
        return new SapBomWip([
            'fg_code' => $row[0],
            'semi_first' => $row[1],
            'qty_first' => $row[2],
            'semi_second' => $row[3] ?? '',
            'qty_second' => $row[4] ?? 0,
            'semi_third' => $row[5] ?? '',
            'qty_third' => $row[6] ?? 0,
            'level' => $row[7],
            'item_group' => $row[8],
        ]);
    }
}
