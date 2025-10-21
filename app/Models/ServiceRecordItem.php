<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRecordItem extends Model
{
    protected $fillable = [
        'service_record_id', 'part_id', 'part_name', 'action', 'condition_before', 'condition_after',
        'qty', 'uom', 'unit_cost', 'discount', 'tax_rate', 'line_total', 'remarks',
    ];

    protected $casts = [
        'qty' => 'float',
        'unit_cost' => 'float',
        'discount' => 'float',
        'tax_rate' => 'float',
    ];

    public function record()
    {
        return $this->belongsTo(ServiceRecord::class, 'service_record_id');
    }
}
