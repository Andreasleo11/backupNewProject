<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SapBomWip extends Model
{
    protected $table = "sap_bom_wip";
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        "fg_code",
        "semi_first",
        "qty_first",
        "semi_second",
        "qty_second",
        "semi_third",
        "qty_third",
        "level",
        "item_group",
    ];
}
