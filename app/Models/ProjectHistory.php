<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'date',
        'status',
        'remarks',
    ];

    public function history()
    {
        return $this->belongsto(ProjectMaster::class);
    }
}
