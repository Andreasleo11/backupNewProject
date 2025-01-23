<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeWarningLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'NIK',
        'warning_type',
        'reason'
    ];
}
