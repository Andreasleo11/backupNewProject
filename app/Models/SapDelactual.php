<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SapDelactual extends Model
{
    protected $table = 'sap_delactual';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = ['item_no', 'delivery_date', 'item_name', 'quantity', 'so_num'];
}
