<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderMaintenanceInventoryReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_dokumen',
        'master_id',
        'revision_date',
    ];

    public function detail()
    {
        return $this->hasMany(DetailMaintenanceInventoryReport::class);
    }

    public function master()
    {
        return $this->belongsTo(MasterInventory::class);
    }
}
