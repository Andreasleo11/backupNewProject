<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentComplianceSnapshot extends Model
{
    protected $fillable = [
        'department_id', 'percent', 'complete_requirements', 'total_requirements', 'generated_at',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
