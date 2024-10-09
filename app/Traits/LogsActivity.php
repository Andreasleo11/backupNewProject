<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public static function bootLogsActivity(){
        static::created(function($model){
            self::logActivity($model, 'created');
        });

        static::updated(function($model){
            self::logActivity($model, 'updated');
        });

        static::deleted(function($model){
            self::logActivity($model, 'deleted');
        });

    }

    protected static function logActivity($model, $action)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'changes' => json_encode($model->getChanges())
        ]);
    }

}
