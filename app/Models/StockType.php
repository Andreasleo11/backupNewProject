<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockType extends Model
{
    use HasFactory;
    protected $table = 'stock_type';

    protected $fillable = [
        'name',
        // Add other fields as needed
    ];
}
