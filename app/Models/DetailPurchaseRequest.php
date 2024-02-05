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
        'unit_price',
    ];

    public function itemDetail()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }
}
