<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdplanKriItem extends Model
{
    use HasFactory;

    protected $fillable = [
        "order_prod",
        "status",
        "item_code",
        "pair_code",
        "material_group",
        "temporary_value",
        "machine_selected",
        "bom_level",
        "continue_prod",
        "lead_time",
        "safety_stock",
        "daily_limit",
        "prod_min",
        "cycle_time_raw",
        "cavity",
        "cycle_time",
        "man_power",
        "setup_time",
        "total_delivery",
        "total_forecast",
        "total_pps",
    ];
}
