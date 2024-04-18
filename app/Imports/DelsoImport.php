<?php

namespace App\Imports;

use App\Models\SapDelso;
use Maatwebsite\Excel\Concerns\ToModel;

class DelsoImport implements ToModel
{

        public function model(array $row)
        {
                /**
            * @param array $row
            * @return \Illuminate\Database\Eloquent\Model|null
            */
            return new SapDelso([
                'doc_num'  => $row[0],
                'doc_status'  => $row[1],
                'item_no'  => $row[2],
                'quantity'  => $row[3],
                'delivered_qty'  => $row[4],
                'line_num'  => $row[5] ?? 0,
                'row_status'  => $row[6],
            ]);
        
        }
    }
