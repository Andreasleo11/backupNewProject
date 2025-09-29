<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForecastMaterialPrediction extends Model
{
    protected $table = 'forecast_material_predictions';

    public $timestamps = false;

    protected $primaryKey = false;

    public $incrementing = false; // bruh need 3 days to get this code

    public function setMonthsAttribute($value)
    {
        // Your logic to handle the setting of the "months" attribute
        $this->attributes['months'] = json_encode($value);
    }

    protected $fillable = [
        'material_code',
        'material_name',
        'customer',
        'item_no',
        'unit_of_measure',
        'quantity_material',
        'vendor_code',
        'quantity_forecast',
        'vendor_name',
        'months',
        // Add other attributes as needed
    ];

    protected $casts = [
        'months' => 'json',
        'quantity_forecast' => 'json',
    ];

    public function inventoryFgs()
    {
        return $this->belongsTo(sapInventoryFg::class, 'item_no', 'item_code');
    }
}
