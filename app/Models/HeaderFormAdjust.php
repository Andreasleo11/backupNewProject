<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderFormAdjust extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'report_id',
        'autograph_1',
        'autograph_2',
        'autograph_3',
        'autograph_4',
        'autograph_5',
        'autograph_6',
        'autograph_7',
    ];

    public function evaluationData()
    {
        return $this->hasMany(FormAdjustMaster::class, 'header_id', 'id');
    }

    public function report()
    {
        return $this->hasOne(Report::class, 'id', 'report_id');
    }
}
