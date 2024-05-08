<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormAdjustMaster extends Model
{
    use HasFactory;

    protected $fillable = [
        'detail_id',
        'header_id',
        'rm_code',
        'rm_description',
        'rm_quantity',
        'fg_measure',
        'rm_measure',
        'warehouse_name',
        'remark',
    ];

    public function detail()
    {
        return $this->belongsTo(Detail::class);
    }
}
