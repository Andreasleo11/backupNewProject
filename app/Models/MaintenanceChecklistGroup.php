<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceChecklistGroup extends Model
{
    use HasFactory;

    protected $table = 'maintenance_checklist_groups';

    protected $fillable = ['name'];

    public function items()
    {
        return $this->hasMany(MaintenanceChecklistItem::class, 'group_id');
    }
}
