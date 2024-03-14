<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtiHolidayList extends Model
{
    public $timestamps = false;
    protected $table = 'uti_holiday_list';

    protected $fillable = [
        'date',
        'holiday_name',
        'description',
        'half_day',
    ];
}
