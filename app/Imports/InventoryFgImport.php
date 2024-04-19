<?php

namespace App\Imports;

use App\Models\SapInventoryFg;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithUpserts;

class InventoryFgImport implements ToModel, WithUpserts
{

        public function model(array $row)
        {
                /**
            * @param array $row
            * @return \Illuminate\Database\Eloquent\Model|null
            */
            return new SapInventoryFg([
                'item_code'=> $row[0],
                'item_name'=> $row[1] ?? '',
                'item_group'=> $row[2] ?? 0,
                'day_set_pps'=> $row[3] ?? 0,
                'setup_time'=> $row[4] ?? 0,
                'cycle_time'=> $row[5] ?? 0.0,
                'cavity'=> $row[6] ?? 0,
                'safety_stock'=> $row[7] ?? 0,
                'daily_limit'=> $row[8] ?? 0,
                'stock'=> $row[9] ?? 0,
                'total_spk'=> $row[10] ?? 0,
                'production_min_qty'=> $row[11] ?? 0,
                'standar_packing'=> $row[12] ?? 0,
                'pair'=> $row[13] ?? '',
                'man_power'=> $row[14] ?? 0,
                'warehouse'=> $row[15] ?? '',
                'process_owner'=> $row[16] ?? '',
                'owner_code'=> $row[17] ?? 0,
                'special_condition'=> $row[18] ?? 0,
                'fg_code_1'=> $row[19] ?? '',
                'fg_code_2'=> $row[20] ?? '',
                'wip_code'=> $row[21] ?? '',
                'material_percentage'=> $row[22] ?? 0,
                'continue_production'=> $row[23] ?? 0, 
                'family'=> $row[24] ?? '',
                'material_group'=> $row[25] ?? '',
                'old_mould'=> $row[26] ?? '',
                'packaging'=> $row[27] ?? '',
                'bom_level'=> $row[28] ?? 0,
            ]);

            
        
        }

        public function uniqueBy()
        {
            return ['item_code']; // Specify the column name(s) to check for duplicates
        }
    }
