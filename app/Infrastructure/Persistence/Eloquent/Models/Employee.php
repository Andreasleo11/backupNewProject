<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Models\Department;
use App\Models\EmployeeWarningLog;
use App\Models\EvaluationData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Employee extends Authenticatable
{
    public $incrementing = false;

    protected $table = 'employees';

    protected $fillable = [
        'nik',
        'name',
        'date_birth',
        'gender',
        'dept_code',
        'position',
        'start_date',
        'branch',
        'employment_type',
        'employment_scheme',
        'grade_code',
        'grade_level',
        'jatah_cuti_tahun',
        'organization_structure',
        'end_date',
    ];

    protected $casts = [
        'date_birth' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'grade_level' => 'integer',
        'jatah_cuti_tahun' => 'integer',
    ];

    public function evaluationData() : HasMany
    {
        return $this->hasMany(EvaluationData::class, 'NIK', 'NIK');
    }

    public function warningLogs(): HasMany
    {
        return $this->hasMany(EmployeeWarningLog::class, 'NIK', 'NIK');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'Dept', 'dept_no');
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}