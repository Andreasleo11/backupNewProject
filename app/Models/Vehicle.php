<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = ['plate_number', 'driver_name', 'brand', 'model', 'year', 'vin', 'odometer', 'status'];

    protected $appends = ['display_name'];

    protected $casts = [
        'year' => 'integer',
        'odometer' => 'integer',
    ];

    public function setPlateNumberAttribute($value)
    {
        $this->attributes['plate_number'] = strtoupper(trim($value));
    }

    public function setVinAttribute($value)
    {
        $this->attributes['vin'] = $value ? strtoupper(trim($value)) : null;
    }

    public function setBrandAttribute($value)
    {
        $this->attributes['brand'] = $value ? trim($value) : null;
    }

    public function setModelAttribute($value)
    {
        $this->attributes['model'] = $value ? trim($value) : null;
    }

    public function serviceRecords()
    {
        return $this->hasMany(ServiceRecord::class)->orderByDesc('service_date');
    }

    public function latestService()
    {
        return $this->hasOne(ServiceRecord::class)->latestOfMany('service_date');
    }

    public function getDisplayNameAttribute(): string
    {   // e.g. "B 1234 XYZ — Avanza (2019)"
        $name = trim($this->brand.' '.$this->model);

        return sprintf('%s — %s (%s)', $this->plate_number, $name ?: 'Vehicle', $this->year ?: 'N/A');
    }
}
