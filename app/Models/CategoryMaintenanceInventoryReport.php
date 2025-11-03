<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryMaintenanceInventoryReport extends Model
{
    use HasFactory;

    protected $fillable = ['group_id', 'name'];

    public function group()
    {
        return $this->belongsTo(GroupMaintenanceInventoryReport::class);
    }
}
