<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailCategoryMaintenanceInventoryReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'name',
    ];

    public function category()
    {
        return $this->belongsTo(CategoryMaintenanceInventoryReport::class);
    }
}
