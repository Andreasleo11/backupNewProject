<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterStock extends Model
{
    use HasFactory;
    protected $table = 'master_stock';

    protected $fillable = [
        'stock_code',
        'stock_description',
        'stock_quantity',
        // Add other fields as needed
    ];
}
