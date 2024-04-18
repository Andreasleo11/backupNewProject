<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sapInventoryFg extends Model
{
    use HasFactory;
    protected $table = 'sap_inventory_fg';

    public $timestamps = false;
    
    protected $fillable = [
        'item_code',
        'item_name',
        'item_group',
        'day_set_pps',
        'setup_time',
        'cycle_time',
        'cavity',
        'safety_stock',
        'daily_limit',
        'stock',
        'total_spk',
        'production_min_qty',
        'standar_packing',
        'pair',
        'man_power',
        'warehouse',
        'process_owner',
        'owner_code',
        'special_condition',
        'fg_code_1',
        'fg_code_2',
        'wip_code',
        'material_percentage',
        'continue_production',
        'family',
        'material_group',
        'old_mould',
        'packaging',
        'bom_level',
    ];
}
