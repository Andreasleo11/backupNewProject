<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = [
        'NIK',
        'Nama',
        'Dept',
        'start_date',
        'status',
        'level',
        'jatah_cuti_taun',
        'end_date',
        'employee_status',
    ];

    public function evaluationData()
    {
        return $this->hasMany(EvaluationData::class, 'NIK', 'NIK');
    }

    public function warningLogs()
    {
        return $this->hasMany(EmployeeWarningLog::class, 'NIK', 'NIK');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'Dept', 'dept_no');
    }
}
