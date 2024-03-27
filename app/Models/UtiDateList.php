<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UtiDateList extends Model
{
    use HasFactory;
    protected $table = 'uti_date_list';
    public $timestamps = false;

    protected $fillable = [
        'start_date',
        'end_date',
    ];
}
