<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDataPartPriceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        "report_id",
        "detail_id",
        "created_by",
        "part_code",
        "currency",
        "price",
    ];
}
