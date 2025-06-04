<?php

namespace App\Models\InspectionForm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionProblem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_report_document_number',
        'type',
        'time',
        'cycle_time',
        'remark'
    ];
}
