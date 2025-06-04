<?php

namespace App\Models\InspectionForm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionPackaging extends Model
{
    use HasFactory;

    protected $fillable = [
        'second_inspection_document_number',
        'quantity',
        'box_label',
        'judgement'
    ];
}
