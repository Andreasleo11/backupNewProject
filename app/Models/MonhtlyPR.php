<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonhtlyPR extends Model
{
    use HasFactory;

    protected $table = 'monthly_pr';

    protected $fillable = [
        'month',
        'year',
        'autograph_1',
        'autograph_2',
        'autograph_3',
        'autograph_user_1',
        'autograph_user_2',
        'autograph_user_3',
        // Add other fields as needed
    ];
}
