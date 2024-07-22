<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterInventory extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'username',
        'dept',
        'type',
        'purpose',
        'brand',
    ];

    public function hardwares()
    {
        return $this->hasMany(DetailHardware::class);
    }

    public function softwares()
    {
        return $this->hasMany(DetailSoftware::class);
    }
}
