<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdplanSndLinecap extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        "running_date",
        "line_code",
        "departement",
        "time_limit_all",
        "time_limit_one",
        "time_limit_two",
        "time_limit_three",
        "running_part",
        "used_time",
    ];
}
