<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Requirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'description', 'allowed_mimetypes', 'min_count', 'validity_days', 'frequency', 'requires_approval',
    ];

    protected $casts = [
        'allowed_mimetypes' => 'array',
        'requires_approval' => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function assignments()
    {
        return $this->hasMany(RequirementAssignment::class);
    }

    public function uploads()
    {
        return $this->hasMany(RequirementUpload::class);
    }
}
