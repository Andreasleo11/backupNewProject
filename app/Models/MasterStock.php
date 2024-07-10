<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterStock extends Model
{
    use HasFactory;
    protected $table = 'master_stock';

    protected $fillable = [
        'stock_type_id',
        'dept_id',
        'stock_code',
        'stock_description',
        'stock_quantity',
        // Add other fields as needed
    ];

    public function stockType()
    {
        return $this->belongsTo(StockType::class, 'stock_type_id');
    }

    public function transactionHistory()
    {
        return $this->hasMany(StockTransaction::class, 'id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id');
    }

    public function requestRelation()
    {
        return $this->hasMany(StockRequest::class, 'stock_id', 'id');
    }

}
