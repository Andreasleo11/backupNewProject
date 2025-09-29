<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sapFctBomWipThird extends Model
{
    protected $table = 'sap_fct_bom_wip_third';

    public $timestamps = false;

    protected $primaryKey = null; // No explicit primary key

    // relation buat insert data semi 3
    public function semiThirdInventoryMtrForecast()
    {
        return $this->hasMany(SapFctInventoryMtr::class, 'fg_code', 'semi_third');
    }
}
