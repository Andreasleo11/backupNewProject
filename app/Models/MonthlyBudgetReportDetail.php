<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MonthlyBudgetReportDetail extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'header_id',
        'name',
        'spec',
        'uom',
        'last_recorded_stock',
        'usage_per_month',
        'quantity',
        'total',
        'remark',
    ];

    public function header()
    {
        return $this->belongsTo(MonthlyBudgetReport::class, 'header_id');
    }
}
