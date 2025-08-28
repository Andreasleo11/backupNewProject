<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaitingPurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        "mold_name",
        "capture_photo_path",
        "process",
        "price",
        "quotation_number",
        "remark",
        "status",
    ];
}
