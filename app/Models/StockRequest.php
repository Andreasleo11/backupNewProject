<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRequest extends Model
{
    protected $table = 'stock_request';

    protected $fillable = [
        'dept_id',
        'stock_id',
        'request_quantity',
        'month',
        'remark',
        // Add other fields as needed
    ];
}
