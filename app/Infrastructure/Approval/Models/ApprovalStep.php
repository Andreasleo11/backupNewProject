<?php

namespace App\Infrastructure\Approval\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalStep extends Model
{
    protected $fillable = [
        'approval_request_id', 'sequence', 'approver_type', 'approver_id', 'status', 'acted_by', 'acted_at', 'remarks',
    ];

    protected $casts = ['acted_at' => 'datetime'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }
}
