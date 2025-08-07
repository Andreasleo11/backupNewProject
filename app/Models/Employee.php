<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    public $incrementing = false;

    protected $fillable = [
        'NIK',
        'Nama',
        'date_birth',
        'Gender',
        'Dept',
        'start_date',
        'status',
        'level',
        'jatah_cuti_tahun',
        'organization_structure',
        'end_date',
        'employee_status',
        'Branch',
        'Grade',
    ];

    protected $casts = [
        'date_birth' => 'date',  // biar otomatis jadi Carbon instance
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
