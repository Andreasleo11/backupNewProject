<?php

namespace App\Models;

use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    protected $table = 'attendance_records';

    protected $fillable = [
        'nik',
        'shift_date',
        'alpha',
        'telat',
        'izin',
        'sakit',
        'synced_at',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'synced_at'  => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'nik', 'nik');
    }
}
