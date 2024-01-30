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
        'Cant_Use',
        'Customer_Defect_Detail',
        'Daijo_Defect_Detail',
        'Remark',
       
        // Add other fields as needed
    ];

    // Define relationships if needed
    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}
