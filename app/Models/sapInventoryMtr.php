<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sapInventoryMtr extends Model
{
    use HasFactory;

    protected $table = 'sap_inventory_mtr';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'fg_code',
        'material_code',
        'material_name',
        'bom_quantity',
        'in_stock',
        'item_group',
        'vendor_code',
        'vendor_name',
    ];
}
