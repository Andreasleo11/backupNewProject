<?php

namespace App\Models\hrd;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportantDocFile extends Model
{
    protected $fillable = ["name", "mime_type", "data"];

    public function importantDoc()
    {
        return $this->belongsTo(ImportantDoc::class);
    }

    use HasFactory;
}
