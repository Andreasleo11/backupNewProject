<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailHardware extends Model
{
    use HasFactory;
    protected $table = 'detail_hardwares';
    protected $fillable = [
        'master_inventory_id',
        'hardware_id',
        'brand',
        'hardware_name',
        'remark',
    ];

    public function masterInventory()
    {
        return $this->belongsTo(MasterInventory::class);
    }

    public function hardwareType()
    {
        return $this->belongsTo(HardwareTypeInventory::class,'hardware_id');
    }
}
