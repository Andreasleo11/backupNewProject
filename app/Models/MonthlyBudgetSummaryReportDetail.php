<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonthlyBudgetSummaryReportDetail extends Model
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
    ];
}
