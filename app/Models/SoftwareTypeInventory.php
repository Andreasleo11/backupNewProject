<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoftwareTypeInventory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function detailSoftware()
    {
        return $this->hasMany(DetailSoftware::class);
    }
}
