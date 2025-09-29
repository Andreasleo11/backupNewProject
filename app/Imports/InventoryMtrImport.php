<?php

namespace App\Imports;

use App\Models\sapInventoryMtr;
use Maatwebsite\Excel\Concerns\ToModel;

class InventoryMtrImport implements ToModel
{
    public function model(array $row)
    {
        /**
         * @param  array  $row
         * @return \Illuminate\Database\Eloquent\Model|null
         */
        return new sapInventoryMtr([
            'fg_code' => $row[0],
            'material_code' => $row[1],
            'material_name' => $row[2],
            'bom_quantity' => $row[3],
            'in_stock' => $row[4],
            'item_group' => $row[5],
            'vendor_code' => $row[6],
            'vendor_name' => $row[7],
        ]);
    }
}
