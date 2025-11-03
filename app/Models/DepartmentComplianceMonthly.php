<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentComplianceMonthly extends Model
{
    protected $fillable = ['department_id', 'month', 'percent'];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
