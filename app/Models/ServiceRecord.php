<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRecord extends Model
{
    protected $fillable = [
        'vehicle_id', 'service_date', 'odometer', 'workshop', 'total_cost', 'notes', 'created_by', 'global_tax_rate',
    ];

    protected $casts = [
        'service_date' => 'date',
        'global_tax_rate' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function items()
    {
        return $this->hasMany(ServiceRecordItem::class);
    }
}
