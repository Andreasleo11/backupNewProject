<?php

namespace App\Models\InspectionForm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FirstInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'detail_inspection_report_document_number',
        'appearance',
        'weight',
        'weight_uom',
        'fitting_test',
    ];
}
