<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HardwareTypeInventory extends Model
{
    use HasFactory;

    protected $fillable = ["name"];

    public function detailHardware()
    {
        return $this->hasMany(DetailHardware::class);
    }
}
