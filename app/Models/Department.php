<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'dept_no', 'is_office'];

    public function users()
    {
        $this->hasMany(User::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'Dept', 'dept_no');
    }
}
