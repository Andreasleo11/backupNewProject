<?php

namespace App\Models\InspectionForm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'detail_inspection_report_document_number',
        'document_number',
        'lot_size_quantity'
    ];

    public function samplingData()
    {
        return $this->hasMany(InspectionSampling::class, 'second_inspection_document_number', 'document_number');
    }

    public function packagingData()
    {
        return $this->hasMany(InspectionPackaging::class, 'second_inspection_document_number', 'document_number');
    }
}
