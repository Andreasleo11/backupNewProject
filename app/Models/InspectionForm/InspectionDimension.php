<?php

namespace App\Models\InspectionForm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionDimension extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_report_document_number',
        'lower_limit',
        'upper_limit',
        'limit_uom',
        'actual_value',
        'start_datetime',
        'end_datetime',
        'judgement',
        'area',
    ];
}
