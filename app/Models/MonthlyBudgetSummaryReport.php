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
        'creator_id',
        'created_autograph',
        'is_known_autograph',
        'approved_autograph'
    ];

    public function details()
    {
        return $this->hasMany(MonthlyBudgetReportSummaryDetail::class, 'header_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
