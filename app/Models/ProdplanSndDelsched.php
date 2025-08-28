<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdplanSndDelsched extends Model
{
    use HasFactory;

    protected $fillable = [
        "delivery_date",
        "actual_deldate",
        "process_date",
        "complete_date",
        "item_code",
        "item_name",
        "item_bom_level",
        "quantity",
        "pair_code",
        "pair_name",
        "pair_bom_level",
        "pair_quantity",
        "prior_item_code",
        "prior_bom_level",
        "final_quantity",
        "completed",
        "outstanding",
        "status",
        "remarks",
        "remarks_leadtime",
        "remarks_actual",
        "color",
        "upcode",
    ];
}
