<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActualOvertimeDetail extends Model
{
    use HasFactory;

    protected $table = 'actual_overtime_details';

    protected $fillable = [
        'key',
        'voucher',
        'in_date',
        'in_time',
        'out_date',
        'out_time',
        'nett_overtime',
    ];

    /**
     * Relasi ke DetailFormOvertime.
     * Asumsinya: 'key' dan 'voucher' mengarah ke field di tabel detail_form_overtime.
     */
    public function detailFormOvertime()
    {
        return $this->belongsTo(DetailFormOvertime::class, 'key', 'id');
    }
}
