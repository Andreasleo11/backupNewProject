<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefectCategory extends Model
{
    protected $fillable = [
        'name'
    ];

    use HasFactory;

    public function defect(){
        return $this->belongsTo(Defect::class);
    }
}
