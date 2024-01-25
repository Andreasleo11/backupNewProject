<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sapFctInventoryMtr extends Model
{
    protected $table = 'sap_fct_inventory_mtr';
    public $timestamps = false;
    protected $primaryKey = null;
    // public $incrementing = false;

    
    public function getInStockAttribute()
    {
        // Perform any additional logic here if needed
        return $this->attributes['in_stock'];
    }

}
