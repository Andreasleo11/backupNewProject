<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sapFctBomWipFgCode extends Model
{
    protected $table = "sap_fct_bom_wip_fgcode";
    public $timestamps = false;
    protected $primaryKey = null; // No explicit primary key

    // relation buat insert data semi 1
    public function rawMaterialFgcode()
    {
        return $this->hasMany(SapFctInventoryMtr::class, "fg_code", "FinishG_Code");
    }
}
