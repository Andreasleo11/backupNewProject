<?php

namespace App\Models\hrd;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportantDoc extends Model
{

    protected $fillable = [
        'name',
        'type_id',
        'expired_date'
    ];

    public function type()
    {
        return $this->belongsTo(ImportantDocType::class, 'type_id', 'id');
    }

    use HasFactory;
}
