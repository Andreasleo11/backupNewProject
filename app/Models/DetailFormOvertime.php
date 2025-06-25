<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailFormOvertime extends Model
{
    protected $table = 'detail_form_overtime';

    protected $fillable = [
        'header_id',
        'NIK',
        'nama',
        'overtime_date',
        'job_desc',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'break',
        'remarks',
    ];

    public function header()
    {
        return $this->belongsTo(HeaderFormOvertime::class, 'header_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'NIK', 'NIK');
    }
}
