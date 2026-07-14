<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Requirement extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'code', 'name', 'description', 'allowed_mimetypes', 'min_count', 'validity_days', 'frequency', 'requires_approval',
    ];

    protected $casts = [
        'allowed_mimetypes' => 'array',
        'requires_approval' => 'boolean',
    ];

    public function assignments()
    {
        return $this->hasMany(RequirementAssignment::class);
    }

    public function uploads()
    {
        return $this->hasMany(RequirementUpload::class);
    }
}
