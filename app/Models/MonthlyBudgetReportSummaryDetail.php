<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonthlyBudgetReportSummaryDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'header_id',
        'name',
        'dept_no',
        'quantity',
        'uom',
        'supplier',
        'cost_per_unit',
        'remark',
        'spec', // for moulding items
        'last_recorded_stock', // for moulding items
        'usage_per_month', // for moulding items
    ];
}
