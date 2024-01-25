<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class foremindFinal extends Model
{
    protected $table = 'foremind_final';

    // Assuming there are no timestamps columns in the table
    public $timestamps = false;
    protected $primaryKey = false;
    public $incrementing = false;

    // Specify the fillable attributes if needed
    protected $fillable = [
        'forecast_code',
        'forecast_name',
        'vendor_code',
        'vendor_name',
        'day_forecast',
        'Item_no',
        'item_name',
        'semi_code',
        'quantity_forecast',
        'item_group',
        'material_code',
        'material_name',
        'quantity_material',
        'quantity_bomWip',
        'material_prediction',
        'U/M',
        // Add other attributes as needed
    ];

    public function sapFctInventoryFg()
    {
        return $this->hasOne(sapFctInventoryFg::class, 'item_code', 'Item_no');
    }
}
