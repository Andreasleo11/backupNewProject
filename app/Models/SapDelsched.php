<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SapDelsched extends Model
{
    protected $table = "sap_delsched";
    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = ["item_code", "delivery_date", "delivery_qty", "so_number"];
}
