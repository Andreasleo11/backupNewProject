<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryRepairHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_id',
        'request_name',
        'action',
        'type',
        'old_part',
        'item_type',
        'item_brand',
        'item_name',
        'action_date',
        'remark',
    ];


    public function masterinventory()
    {
        return $this->belongsTo(MasterInventory::class);
    }

    public function hardwareType()
    {
        return $this->belongsTo(HardwareTypeInventory::class,'item_type');
    }

    public function softwareType()
    {
        return $this->belongsTo(SoftwareTypeInventory::class,'item_type');
    }
}
