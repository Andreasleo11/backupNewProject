<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sapLineProduction extends Model
{
    use HasFactory;
    protected $table = 'sap_lineproduction';
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'item_code',
        'line_production',
        'priority',   
    ];
}
