<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterDataPart extends Model
{
    use HasFactory;

    protected $fillable = ['item_no', 'description', 'item_group', 'active'];

    protected $casts = [
        'item_group' => 'integer',
        'active' => 'boolean',
    ];
}
