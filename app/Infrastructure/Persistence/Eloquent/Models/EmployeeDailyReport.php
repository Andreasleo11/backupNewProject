<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDailyReport extends Model
{
    use HasFactory;

    protected $table = 'employee_daily_reports'; // Nama tabel yang digunakan

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
