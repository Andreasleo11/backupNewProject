<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated This model is deprecated. Use Spatie\Activitylog\Models\Activity instead.
 * This model references the old 'activity_logs' table which is no longer actively used.
 * All new activity logging uses Spatie Activity Log package.
 * Migration date: 2026-01-29
 */
class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'model_type', 'model_id', 'changes'];
}
