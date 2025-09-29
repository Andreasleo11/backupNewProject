<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapDelso extends Model
{
    protected $table = 'sap_delso';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'doc_num',
        'doc_status',
        'item_no',
        'quantity',
        'delivered_qty',
        'line_num',
        'row_status',
    ];
}
