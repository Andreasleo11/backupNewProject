<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Employee extends Authenticatable
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
        'date_birth' => 'date', // biar otomatis jadi Carbon instance
    ];

    public function evaluationData()
    {
        return $this->hasMany(EvaluationData::class, 'NIK', 'nik');
    }

    public function warningLogs()
    {
        return $this->hasMany(EmployeeWarningLog::class, 'NIK', 'nik');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'dept_code', 'dept_no');
    }
}
