<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RequirementAssignment extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'requirement_id', 'scope_type', 'scope_id', 'is_mandatory', 'start_date', 'end_date',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function requirement()
    {
        return $this->belongsTo(Requirement::class);
    }

    public function scope(): MorphTo
    {
        return $this->morphTo();
    }
}
