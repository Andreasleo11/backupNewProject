<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sapForecast extends Model
{
    protected $table = 'sap_forecast';
    public $timestamps = false;
    // public $incrementing = false;
    protected $primaryKey = null; 

    // Relationship buat data tanpa wip -> langsung raw material 
    public function inventoryMtr() {
        return $this->hasMany(SapFctInventoryMtr::class, 'fg_code', 'item_no');
    }
    //ga kepake
    public function bomWip() {
        return $this->hasMany(sapFctBomWipFgCode::class, 'FinishG_Code', 'item_no');
    }

    public function firstBomWip() {
        return $this->hasMany(SapFctBomWipFirst::class, 'fg_code', 'item_no');
    }

    public function secondBomWip() {
        return $this->hasMany(SapFctBomWipSecond::class, 'fg_code', 'item_no');
    }

    public function thirdBomWip() {
        return $this->hasMany(SapFctBomWipThird::class, 'fg_code', 'item_no');
    }

}