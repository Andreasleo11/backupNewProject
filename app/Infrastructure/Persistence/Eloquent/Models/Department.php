<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Models\RequirementAssignment;
use App\Models\RequirementUpload;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Department extends Model
{
    use HasFactory;

    protected $table = 'departments';

    protected $fillable = ['dept_no', 'name', 'code', 'branch', 'is_office', 'is_active'];

    protected $casts = [
        'is_office' => 'bool',
        'is_active' => 'bool',
    ];

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