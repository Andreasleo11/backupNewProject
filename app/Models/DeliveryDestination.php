<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryDestination extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'delivery_note_id',
        'destination',
        'remarks',
        'driver_cost',
        'kenek_cost',
        'balikan_cost',
        'driver_cost_currency',
        'kenek_cost_currency',
        'balikan_cost_currency',
    ];

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function deliveryOrders()
    {
        return $this->hasMany(DeliveryDestinationOrder::class);
    }
}
