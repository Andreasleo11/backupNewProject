<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [

        'report_id',
        'part_name',
        'rec_quantity',
        'verify_quantity',
        'can_use',
        'cant_use',
        'price',
        'do_num',
        'fg_measure',
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

    public function adjustdetail()
    {
        return $this->hasMany(FormAdjustMaster::class, 'detail_id','id');
    }

    public function adjustheader()
    {
        return $this->hasMany(HeaderFormAdjust::class);
    }

}
