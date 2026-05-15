<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class LockerIncident extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'locker_assignment_id',
        'type',
        'fine_amount',
        'is_paid',
        'reported_at',
        'notes',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'is_paid' => 'boolean',
        'fine_amount' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(LockerAssignment::class, 'locker_assignment_id');
    }
}
