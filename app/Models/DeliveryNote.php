<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeliveryNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch',
        'ritasi',
        'delivery_note_date',
        'departure_time',
        'return_time',
        'vehicle_number',
        'driver_name',
        'approval_flow_id',
        'status',
        
    ];

    protected $casts = [
        'branch' => 'string',
        'status' => 'string',
        'delivery_note_date' => 'date',
        'departure_time' => 'datetime:H:i',
        'return_time' => 'datetime:H:i'
    ];

    public function destinations()
    {
        return $this->hasMany(DeliveryDestination::class);
    }
}
