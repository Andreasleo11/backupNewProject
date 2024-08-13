<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailSoftware extends Model
{
    use HasFactory;
    protected $table = 'detail_softwares';
    protected $fillable = [
        'master_inventory_id',
        'software_id',
        'software_brand',
        'software_name',
        'license',
        'remark',
    ];

    public function masterInventory()
    {
        return $this->belongsTo(MasterInventory::class);
    }

    public function softwareType()
    {
        return $this->belongsTo(SoftwareTypeInventory::class, 'software_id');
    }
    
}
