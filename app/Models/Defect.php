<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Defect extends Model
{
    use HasFactory;

    protected $fillable = [
        'detail_id',
        'category_id',
        'is_daijo',
        'quantity',
        'remarks',
    ];

    public function detail()
    {
        return $this->belongsTo(Detail::class);
    }

    public function category()
    {
        return $this->hasOne(DefectCategory::class ,'id', 'category_id');
    }
}

