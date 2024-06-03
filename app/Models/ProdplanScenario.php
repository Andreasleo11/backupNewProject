<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdplanScenario extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'val_int_inj',
        'val_vc_inj',
        'val_int_snd',
        'val_vc_snd',
        'val_int_asm',
        'val_vc_asm',
        'val_int+kri',
        'val_vc_kri',
    ];
    
}
