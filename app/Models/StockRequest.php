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
        'quantity_available',
        'month',
        'remark',
        // Add other fields as needed
    ];
    
    public function deptRelation()
    {
        return $this->belongsTo(Department::class, 'dept_id');
    }

    public function stockRelation()
    {
        return $this->belongsTo(MasterStock::class, 'stock_id');
    }
}
