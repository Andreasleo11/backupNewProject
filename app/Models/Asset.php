<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Asset extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'brand',
        'asset_tag',
        'serial_number',
        'category_id',
        'status',
        'location_id',
        'assigned_to_user_id',
        'assigned_to_nik',
        'purchase_date',
        'purchase_cost',
        'warranty_expiry',
        'notes',
        'ip_address',
        'username',
        'purpose',
        'os',
        'position_image',
        'department_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(\App\Infrastructure\Persistence\Eloquent\Models\Employee::class, 'assigned_to_nik', 'nik');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Infrastructure\Persistence\Eloquent\Models\Department::class, 'department_id');
    }

    public function components(): HasMany
    {
        return $this->hasMany(AssetComponent::class);
    }

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(AssetServiceRecord::class);
    }
}
