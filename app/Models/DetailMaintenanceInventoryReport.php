<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailMaintenanceInventoryReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'header_id',
        'category_id',
        'condition',
        'remark',
        'checked_by',
    ];

    public function typecategory()
    {
        return $this->belongsTo(CategoryMaintenanceInventoryReport::class, 'category_id');
    }
}
