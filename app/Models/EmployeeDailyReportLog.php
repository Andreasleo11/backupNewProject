<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDailyReportLog extends Model
{
    public $timestamps = false;
    use HasFactory;

    protected $fillable = [
        "logged_at",
        "employee_id",
        "employee_name",
        "department_id",
        "work_date",
        "work_time",
        "report_type",
        "work_description",
        "proof_url",
        "status",
        "message",
    ];
}
