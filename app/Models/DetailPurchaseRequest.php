<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPurchaseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_request_id',
        'item_name',
        'quantity',
        'purpose',
        'price',
    ];

    public function itemDetail()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function master()
    {
        return $this->hasOne(MasterDataPr::class , 'name', 'item_name');
    }
}
