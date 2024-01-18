<?php

namespace App\Models\hrd;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportantDoc extends Model
{

    protected $fillable = [
        'name',
        'type',
        'expired_date'
    ];

    use HasFactory;
}
