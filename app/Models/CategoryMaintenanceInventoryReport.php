<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryMaintenanceInventoryReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function detail()
    {
        return $this->hasMany(DetailCategoryMaintenanceInventoryReport::class);
    }
}
