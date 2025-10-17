<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRecordItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_record_id', 'part_id', 'part_name', 'action', 'condition_before', 'condition_after',
        'qty', 'uom', 'unit_cost', 'discount', 'line_total', 'remarks',
    ];

    public function record()
    {
        return $this->belongsTo(ServiceRecord::class, 'service_record_id');
    }
}
