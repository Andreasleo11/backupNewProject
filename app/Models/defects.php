<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class defects extends Model
{
    use HasFactory;

    protected $fillable = [
        'detail_id',
        'category_id',
        'is_daijo',
        'quantity',
        'remarks',
    ];

    public function detaildefect()
    {
        return $this->belongsTo(Detail::class);
    }

    public function defectcategory()
    {
        return $this->hasMany(DefectCategory::class);
    }
}

