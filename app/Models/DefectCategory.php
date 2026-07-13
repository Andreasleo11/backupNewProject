<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class DefectCategory
 * @package App\Models
 * @deprecated This model is deprecated in favor of App\Infrastructure\Persistence\Eloquent\Models\DefectCatalog.
 */
class DefectCategory extends Model
{
    protected $fillable = ['name'];

    use HasFactory;

    public function defect()
    {
        return $this->belongsTo(Defect::class);
    }
}
