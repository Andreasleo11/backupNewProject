<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalStep extends Model
{
    protected $fillable = [
        'approval_request_id', 'sequence', 'approver_type', 'approver_id', 'status', 'acted_by', 'acted_at', 'remarks', 'user_signature_id', 'signature_image_path', 'signature_sha256',
    ];

    protected $casts = ['acted_at' => 'datetime'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }
}
