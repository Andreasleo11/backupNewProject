<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDailyReportLog extends Model
{
    public $timestamps = false;

    use HasFactory;

    protected $fillable = [
        'logged_at',
        'employee_id',
        'work_date',
        'work_time',
        'work_description',
        'proof_url',
        'status',
        'message',
    ];
}
