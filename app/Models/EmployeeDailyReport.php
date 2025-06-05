<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDailyReport extends Model
{
    use HasFactory;

    protected $table = 'employee_daily_reports'; // Nama tabel yang digunakan

    protected $fillable = [
        'submitted_at',
        'report_type',
        'employee_id',
        'departement_id',
        'employee_name',
        'work_date',
        'work_time',
        'work_description',
        'proof_url',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'work_date' => 'date',
    ];

}
