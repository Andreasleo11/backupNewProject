<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailPurchaseRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'purchase_request_id',
        'item_name',
        'quantity',
        'purpose',
        'price',
        'is_approve_by_head',
        'is_approve_by_verificator',
        'is_approve',
        'is_approve_by_gm',
        'uom',
        'currency',
        'received_quantity'
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
