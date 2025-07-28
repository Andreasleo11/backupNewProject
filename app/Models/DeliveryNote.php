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
    ];

    public function getRitasiLabelAttribute()
    {
        $labels = [
            1 => 'Pagi',
            2 => 'Siang',
            3 => 'Sore',
            4 => 'Malam',
        ];

        return $this->ritasi
            ? $this->ritasi . ' (' . ($labels[$this->ritasi] ?? '-') . ')'
            : '-';
    }

    public function getFormattedDeliveryNoteDateAttribute()
    {
        return \Carbon\Carbon::parse($this->delivery_note_date)->format('d-m-Y');
    }

    public function getFormattedDepartureTimeAttribute()
    {
        return $this->departure_time
            ? \Carbon\Carbon::createFromFormat('H:i:s', $this->departure_time)->format('H:i')
            : '-';
    }

    public function getFormattedReturnTimeAttribute()
    {
        return $this->return_time
            ? \Carbon\Carbon::createFromFormat('H:i:s', $this->return_time)->format('H:i')
            : '-';
    }

    public function destinations()
    {
        return $this->hasMany(DeliveryDestination::class);
    }
}
