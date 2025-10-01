<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sapFctBomWipFirst extends Model
{
    protected $table = 'sap_fct_bom_wip_first';

    public $timestamps = false;

    protected $primaryKey = null; // No explicit primary key

    // relation buat insert data semi 1
    public function semiFirstInventoryMtrForecast()
    {
        return $this->hasMany(SapFctInventoryMtr::class, 'fg_code', 'semi_first');
    }
}
