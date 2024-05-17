<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderFormOvertime extends Model
{
    protected $table = 'header_form_overtime';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'dept_id',
        'create_date',
        'autograph_1',
        'autograph_2',
        'autograph_3',
        'autograph_4',
        'is_approve',
        'status',
    ];
}
