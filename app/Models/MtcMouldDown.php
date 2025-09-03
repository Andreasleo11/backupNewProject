<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MtcMouldDown extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $fillable = ["mould_code", "date_down", "date_prediction"];
}
