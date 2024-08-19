<?php

namespace App\Imports;

use App\Models\SapReject;
use Maatwebsite\Excel\Concerns\ToModel;

class SapRejectImport implements ToModel
{

        public function model(array $row)
        {
                /**
            * @param array $row
            * @return \Illuminate\Database\Eloquent\Model|null
            */
            return new SapReject([
                'item_no'  => $row[0],
                'warehouse'  => $row[1],
                'in_stock'  => $row[2],
            ]);
        
        }
    }
