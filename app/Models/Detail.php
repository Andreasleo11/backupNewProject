<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail extends Model
{
    use HasFactory;

    protected $fillable = [

        'report_id',
        'part_name',
        'rec_quantity',
        'verify_quantity',
        'can_use',
        'cant_use',
    ];

    // Define relationships if needed
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    public function defects()
    {
        return $this->hasMany(Defect::class);
    }
}
