<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalStep extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     *
     * @see ApprovalRequest::newFactory() for explanation
     */
    protected static function newFactory()
    {
        return \Database\Factories\Infrastructure\Persistence\Eloquent\Models\ApprovalStepFactory::new();
    }

    protected $fillable = [
        'approval_request_id', 'sequence', 'approver_type', 'approver_id', 'status', 'acted_by', 'acted_at', 'remarks', 'user_signature_id', 'signature_image_path', 'signature_sha256',
    ];

    protected $casts = ['acted_at' => 'datetime'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }
}
