<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Locker extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['locker_number', 'location', 'status'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LockerAssignment::class);
    }

    public function currentAssignment(): HasOne
    {
        return $this->hasOne(LockerAssignment::class)->whereNull('released_at')->latestOfMany();
    }
}
