<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdplanKriDelraw extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_date',
        'process_owner',
        'bom_level',
        'item_code',
        'item_pair',
        'item_fg',
        'asm_on_line',
        'fg_code_line',
        'quantity',
    ];
}
