<?php

namespace App\Models\InspectionForm;

use App\Models\InspectionQuantity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionReport extends Model
{
    use HasFactory;

    protected $fillable = [
        "document_number",
        "customer",
        "inspection_date",
        "part_number",
        "part_name",
        "weight",
        "weight_uom",
        "material",
        "color",
        "tool_number_or_cav_number",
        "machine_number",
        "shift",
        "operator",
        "inspector_autograph",
        "leader_autograph",
        "head_autograph",
        "inspector",
    ];

    public function detailInspectionReports()
    {
        return $this->hasMany(
            DetailInspectionReport::class,
            "inspection_report_document_number",
            "document_number",
        );
    }

    public function dimensionData()
    {
        return $this->hasMany(
            InspectionDimension::class,
            "inspection_report_document_number",
            "document_number",
        );
    }

    public function problemData()
    {
        return $this->hasMany(
            InspectionProblem::class,
            "inspection_report_document_number",
            "document_number",
        );
    }

    public function quantityData()
    {
        return $this->hasOne(
            InspectionQuantity::class,
            "inspection_report_document_number",
            "document_number",
        );
    }
}
