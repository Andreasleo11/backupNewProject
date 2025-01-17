<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Defect extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'detail_id',
        'category_id',
        'is_daijo',
        'is_customer',
        'is_supplier',
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

