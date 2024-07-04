<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarcodePackagingMaster extends Model
{
    protected $table = 'barcode_packaging_master';

    protected $fillable = [
        'noDokumen',
        'dateScan',
        'tipeBarcode',
        'location',
    ];

    
    public function detailBarcode()
    {
        return $this->hasMany(BarcodePackagingDetail::class, 'masterId', 'id');
    }
}
