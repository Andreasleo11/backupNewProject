<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalAction extends Model
{
    protected $fillable = ['approval_request_id', 'user_id', 'from_status', 'to_status', 'remarks'];

    public function causer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function getDescriptionAttribute(): string
    {
        $desc = "changed status from {$this->from_status} to {$this->to_status}";

        return $desc;
    }

    public function getPropertiesAttribute(): \Illuminate\Support\Collection
    {
        $attrs = [];
        if ($this->remarks) {
            $attrs['remarks'] = $this->remarks;
        }

        return collect(['attributes' => $attrs]);
    }
}
