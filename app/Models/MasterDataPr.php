<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDataPr extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'currency',
        'price',
        'latest_price',
    ];

    public function details(){
        $this->belongsTo(DetailPurchaseRequest::class, 'name', 'item_name');
    }
}
