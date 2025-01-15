<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SapReject extends Model
{
    protected $table = 'sap_reject';
    use HasFactory;
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'item_no',
        'item_description',
        'item_group',
        'in_stock',   
    ];
}
