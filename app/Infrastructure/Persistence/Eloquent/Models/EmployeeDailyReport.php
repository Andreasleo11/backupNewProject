<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class EmployeeDailyReport extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'employee_daily_reports'; // Nama tabel yang digunakan

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'nik');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->useLogName('daily_report');
    }

    protected $fillable = [
        'submitted_at',
        'employee_id',
        'work_date',
        'work_time',
        'work_description',
        'proof_url',
        'sort_datetime',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'work_date' => 'date',
        'sort_datetime' => 'datetime',
    ];
}
