<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdplanSndLinelist extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        "area",
        "line_code",
        "daily_minutes",
        "running_part",
        "material_group",
        "continue_running",
        "status",
        "start_repair",
        "end_repair",
    ];
}
