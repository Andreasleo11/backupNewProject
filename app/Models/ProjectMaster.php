<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_name',
        'dept',
        'request_date',
        'start_date',
        'end_date',
        'pic',
        'description',
        'status',
        // Add other fields as needed
    ];

    public function prohist()
    {
        return $this->hasMany(ProjectHistory::class);
    }
}
