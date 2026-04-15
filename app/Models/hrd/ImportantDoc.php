<?php

namespace App\Models\hrd;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImportantDoc extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'type_id', 'expired_date', 'document_id', 'description'];

    protected $casts = [
        'expired_date' => 'date',
    ];

    public function type()
    {
        return $this->belongsTo(ImportantDocType::class, 'type_id', 'id');
    }

    public function files()
    {
        return $this->hasMany(ImportantDocFile::class);
    }

    use HasFactory;
}
