<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

/**
 * @deprecated This trait is deprecated. Use Spatie\Activitylog\Traits\LogsActivity instead.
 * All models have been migrated to use Spatie's activity logging.
 * Migration date: 2026-01-29
 * 
 * Models that previously used this trait:
 * - App\Models\Detail
 * - App\Models\DetailPurchaseRequest
 * - App\Models\File
 * - App\Models\Report
 * - App\Models\PurchaseRequest
 * - App\Models\Defect
 */
trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($model) {
            self::logActivity($model, 'created');
        });

        static::updated(function ($model) {
            self::logActivity($model, 'updated');
        });

        static::deleted(function ($model) {
            self::logActivity($model, 'deleted');
        });
    }

    protected static function logActivity($model, $action)
    {
        ActivityLog::create([
            'user_id' => Auth::id() ?? 0, // Fallback to system user when no auth context
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'changes' => json_encode($model->getChanges()),
        ]);
    }
}
