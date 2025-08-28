<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionJudgement extends Model
{
    use HasFactory;

    protected $fillable = [
        "detail_inspection_report_document_number",
        "pass_quantity",
        "reject_quantity",
    ];
}
