<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RequirementUpload extends Model
{
    protected $fillable = [
        'requirement_id', 'scope_type', 'scope_id', 'path', 'original_name', 'mime_type', 'size', 'uploaded_by', 'valid_from', 'valid_until', 'status', 'review_notes',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_until' => 'date',
    ];

    protected static function booted()
    {
        static::created(function ($upload) {
            if ($upload->scope_type === \App\Models\Department::class) {
                \App\Jobs\UpdateDepartmentComplianceSnapshot::dispatch($upload->scope_id);
            }
        });

        static::updated(function ($upload) {
            if ($upload->isDirty('status') && $upload->scope_type === \App\Models\Department::class) {
                \App\Jobs\UpdateDepartmentComplianceSnapshot::dispatch($upload->scope_id);
            }
        });

        static::deleted(function ($upload) {
            if ($upload->scope_type === \App\Models\Department::class) {
                \App\Jobs\UpdateDepartmentComplianceSnapshot::dispatch($upload->scope_id);
            }
        });
    }

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function scope(): MorphTo
    {
        return $this->morphTo();
    }

    public function isCurrentlyValid(): bool
    {
        $today = Carbon::today();
        if ($this->valid_from && $today->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_until && $today->gt($this->valid_until)) {
            return false;
        }
        if ($this->requirement?->requires_approval && $this->status !== 'approved') {
            return false;
        }

        return true;
    }

    public function uploadedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }
}
