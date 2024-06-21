<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonthlyBudgetSummaryReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'report_date',
        'created_autograph',
        'is_known_autograph',
        'approved_autograph'
    ];

    public function details()
    {
        return $this->hasMany(MonthlyBudgetReportSummaryDetails::class, 'header_id');
    }
}
