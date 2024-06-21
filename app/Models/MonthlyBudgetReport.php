<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\MonthlyBudgetReportDetail;

class MonthlyBudgetReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'dept_no',
        'creator_id',
        'report_date',
        'created_autograph',
        'is_known_autograph',
        'approved_autograph',
    ];

    public function details()
    {
        return $this->hasMany(MonthlyBudgetReportDetail::class, 'header_id');
    }

    public function department()
    {
        return $this->hasOne(Department::class, 'dept_no', 'dept_no');
    }
}
