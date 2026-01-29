<?php

namespace App\Models;

use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Defect extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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
        return $this->hasOne(DefectCategory::class, 'id', 'category_id');
    }
}
