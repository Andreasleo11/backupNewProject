<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class sapFctBomWip extends Model
{
    protected $table = 'sap_fct_bom_wip';

    public $timestamps = false;

    protected $primaryKey = null; // No explicit primary key

    // Define relationships

    // public function fgRawInventoryMtr() {
    //     return $this->hasMany(sapFctInventoryMtr::class, 'fg_code', 'fg_code');
    // }

    // public function semiFirstInventoryMtr() {
    //     return $this->hasMany(SapFctInventoryMtr::class, 'fg_code', 'semi_first');
    // }

    // public function semiSecondInventoryMtr() {
    //     return $this->hasMany(SapFctInventoryMtr::class, 'fg_code', 'semi_second');
    // }

    // public function semiThirdInventoryMtr() {
    //     return $this->hasMany(SapFctInventoryMtr::class, 'fg_code', 'semi_third');
    // }

    // public function joinInventoryMtr($semiType)
    // {
    //     $query = $this->select('sap_fct_bom_wip.*', 'sap_fct_inventory_mtr.*')
    //         ->leftJoin('sap_fct_inventory_mtr', function ($join) use ($semiType) {
    //             $join->on('sap_fct_bom_wip.fg_code', '=', 'sap_fct_inventory_mtr.fg_code')
    //                 ->where(function ($query) use ($semiType) {
    //                     if ($semiType === 'semi_first') {
    //                         $query->where('sap_fct_bom_wip.semi_first', '=', 'sap_fct_inventory_mtr.fg_code');
    //                     } elseif ($semiType === 'semi_second') {
    //                         $query->where('sap_fct_bom_wip.semi_second', '=', 'sap_fct_inventory_mtr.fg_code');
    //                     } elseif ($semiType === 'semi_third') {
    //                         $query->where('sap_fct_bom_wip.semi_third', '=', 'sap_fct_inventory_mtr.fg_code');
    //                     }
    //                 });
    //         });

    //     return $query->get();
    // }
}
