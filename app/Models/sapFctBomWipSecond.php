<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sapFctBomWipSecond extends Model
{
    protected $table = 'sap_fct_bom_wip_second';

    public $timestamps = false;

    protected $primaryKey = null; // No explicit primary key

    // relation buat insert data semi 2
    public function semiSecondInventoryMtrForecast()
    {
        return $this->hasMany(SapFctInventoryMtr::class, 'fg_code', 'semi_second');
    }
}
