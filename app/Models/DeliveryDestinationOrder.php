<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryDestinationOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ["delivery_destination_id", "delivery_order_number"];

    public function destination()
    {
        return $this->belongsTo(DeliveryDestination::class);
    }
}
