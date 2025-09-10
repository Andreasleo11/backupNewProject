<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonthlyBudgetReportDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "header_id",
        "name",
        "spec",
        "uom",
        "last_recorded_stock",
        "usage_per_month",
        "quantity",
        "total",
        "remark",
    ];

    public function header()
    {
        return $this->belongsTo(MonthlyBudgetReport::class, "header_id");
    }
}
