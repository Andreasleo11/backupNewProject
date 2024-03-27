<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    //! AFTER UPDATE PLEASE DELETE THIS
    // protected $fillable = [
    //     'name'
    // ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
