<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionQuantity extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_report_document_number',
        'output_quantity',
        'pass_quantity',
        'reject_quantity',
        'sampling_quantity',
        'ng_sample_quantity',
        'pass_rate',
        'reject_rate',
        'ng_sample_rate',
    ];
    
}
