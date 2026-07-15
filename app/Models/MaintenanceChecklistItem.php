<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceChecklistItem extends Model
{
    use HasFactory;

    protected $table = 'maintenance_checklist_items';

    protected $fillable = ['group_id', 'name'];

    public function group()
    {
        return $this->belongsTo(MaintenanceChecklistGroup::class, 'group_id');
    }
}
