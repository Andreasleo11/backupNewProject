<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $table = 'stock_transaction';

    protected $fillable = [
        'unique_code',
        'stock_id',
        'dept_id',
        'in_time',
        'is_out',
        'is_return',
        'receiver',
        'remark',
        'out_time',
        // Add other fields as needed
    ];

    public function historyTransaction()
    {
        return $this->belongsTo(MasterStock::class, 'stock_id');
    }
}
