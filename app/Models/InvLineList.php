<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvLineList extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = [
        'line_code',
        'line_name',
        'departement',
        'daily_minutes',
    ];
}
