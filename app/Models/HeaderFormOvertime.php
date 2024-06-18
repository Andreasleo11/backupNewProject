<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderFormOvertime extends Model
{
    protected $table = 'header_form_overtime';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'dept_id',
        'create_date',
        'autograph_1',
        'autograph_2',
        'autograph_3',
        'autograph_4',
        'is_approve',
        'status',
        'is_design',
        'description',
    ];

    public function Relationuser()
    {
        return $this->hasone(User::class, 'id', 'user_id');
    }

    public function Relationdepartement()
    {
        return $this->hasone(Department::class, 'id', 'dept_id');
    }

    public function details()
    {
        return $this->hasMany(DetailFormOvertime::class, 'header_id', 'id');
    }
}
