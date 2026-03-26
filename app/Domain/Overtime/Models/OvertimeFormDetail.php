<?php

namespace App\Domain\Overtime\Models;

use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Models\ActualOvertimeDetail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OvertimeFormDetail extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'detail_form_overtime';

    protected $fillable = [
        'header_id',
        'NIK',
        'name',
        'overtime_date',
        'job_desc',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'break',
        'remarks',
        'status',
        'reason', // Added reason field
    ];

    public function header()
    {
        return $this->belongsTo(OvertimeForm::class, 'header_id', 'id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'NIK', 'nik');
    }

    public function actualOvertimeDetail()
    {
        return $this->hasOne(ActualOvertimeDetail::class, 'key', 'id');
    }
}

