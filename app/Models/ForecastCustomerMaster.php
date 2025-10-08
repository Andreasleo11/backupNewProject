<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastCustomerMaster extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['forecast_code', 'forecast_name', 'customer'];
}
