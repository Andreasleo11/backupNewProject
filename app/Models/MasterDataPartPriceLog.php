<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDataPartPriceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        // Legacy columns — still populated for old records, no FK constraint after migration
        'report_id',
        'detail_id',
        // New columns — used for VerificationReport/VerificationItem records
        'verification_report_id',
        'verification_item_id',
        'created_by',
        'part_code',
        'currency',
        'price',
    ];
}
