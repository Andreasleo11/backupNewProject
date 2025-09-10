<?php

namespace App\Models\hrd;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportantDocType extends Model
{
    protected $fillable = ["name"];

    public function importantDocs()
    {
        return $this->hasMany(ImportantDoc::class, "type_id", "id");
    }

    use HasFactory;
}
