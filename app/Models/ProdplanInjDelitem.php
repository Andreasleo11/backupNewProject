<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdplanInjDelitem extends Model
{
    use HasFactory;

    protected $fillable = [
        "item_code",
        "item_bom_level",
        "item_pair",
        "pair_bom_level",
        "item_wip",
        "bom_level",
    ];
}
