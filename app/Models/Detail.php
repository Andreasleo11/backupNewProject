<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail extends Model
{
    use HasFactory;

    protected $fillable = [
        'Report_Id',
        'Part_Name',
        'Rec_Quantity',
        'Verify_Quantity',
        'Prod_Date',
        'Shift',
        'Can_Use',
        'Customer_Defect',
        'Daijo_Defect',
        'Customer_Defect_Detail',
        'Remark_Customer',
        'Daijo_Defect_Detail',
        'Remark_Daijo',
        // Add other fields as needed
    ];

    // Define relationships if needed
    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
