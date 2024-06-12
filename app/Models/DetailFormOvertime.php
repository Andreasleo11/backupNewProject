<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailFormOvertime extends Model
{
    protected $table = 'detail_form_overtime';
    public $timestamps = false;

    protected $fillable = [
        'header_id',
        'NIK',
        'nama',
        'job_desc',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'break',
        'remarks',
    ];
}
