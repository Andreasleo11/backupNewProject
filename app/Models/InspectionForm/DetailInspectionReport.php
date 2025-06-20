<?php

namespace App\Models\InspectionForm;

use App\Models\InspectionJudgement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailInspectionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_report_document_number',
        'document_number',
        'period',
        'start_datetime',
        'end_datetime',
    ];

    public function firstInspections()
    {
        return $this->hasMany(FirstInspection::class, 'detail_inspection_report_document_number', 'document_number');
    }

    public function secondInspections()
    {
        return $this->hasMany(SecondInspection::class, 'detail_inspection_report_document_number', 'document_number');
    }

    public function judgementData()
    {
        return $this->hasMany(InspectionJudgement::class, 'detail_inspection_report_document_number', 'document_number');
    }
}
