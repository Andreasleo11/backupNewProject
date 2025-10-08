<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarcodePackagingDetail extends Model
{
    protected $table = 'barcode_packaging_details';

    protected $fillable = [
        'masterId',
        'noDokumen',
        'partNo',
        'quantity',
        'label',
        'position',
        'scantime',
    ];

    public function masterBarcode()
    {
        return $this->belongsTo(BarcodePackagingMaster::class, 'masterId', 'id');
    }
}
