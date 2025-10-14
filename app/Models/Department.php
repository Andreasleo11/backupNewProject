<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'dept_no', 'is_office'];

    public function users()
    {
        $this->hasMany(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'name';
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'Dept', 'dept_no');
    }

    public function requirementAssignments(): MorphMany
    {
        return $this->morphMany(RequirementAssignment::class, 'scope');
    }

    public function requirementUploads(): MorphMany
    {
        return $this->morphMany(RequirementUpload::class, 'scope');
    }
}
