<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_number',
        'code',
        'name',
        'category_name',
        'category_code',
        'uom',
        'quantity',
        'currency',
        'price',
        'dept_code',
    ];
}
