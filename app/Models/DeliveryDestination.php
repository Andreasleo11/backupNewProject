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
        'delivery_order_number',
        'remarks',
        'cost',
        'cost_currency',
    ];

    public function deliveryNote()
    {
        return $this->belongsTo(DeliveryNote::class);
    }
}
