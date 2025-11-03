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
        'qty' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
    ];

    public function record()
    {
        return $this->belongsTo(ServiceRecord::class, 'service_record_id');
    }
}
