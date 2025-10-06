<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RequirementAssignment extends Model
{
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
