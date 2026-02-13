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
        'approval_request_id',
        'sequence',
        'approver_type',
        'approver_id',
        'approver_snapshot_name',
        'approver_snapshot_role_slug',
        'approver_snapshot_label',
        'status',
        'acted_by',
        'acted_at',
        'remarks',
        'user_signature_id',
        'signature_image_path',
        'signature_sha256',
    ];

    protected $casts = ['acted_at' => 'datetime'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ApprovalRequest::class, 'approval_request_id');
    }

    public function actedUser(): BelongsTo
    {
        return $this->belongsTo(\App\Infrastructure\Persistence\Eloquent\Models\User::class, 'acted_by');
    }

    /**
     * Map the role slug to an approver type for item validation.
     */
    public function getItemApproverTypeAttribute(): ?string
    {
        $slug = $this->approver_snapshot_role_slug;

        // Fallback for legacy data
        if (! $slug && $this->approver_type === 'role') {
            $role = \Spatie\Permission\Models\Role::find($this->approver_id);
            $slug = $role?->name;
        }
        
        return match ($slug) {
            'pr-dept-head' => 'head',
            'pr-verificator' => 'verificator',
            'pr-director' => 'director',
            'pr-purchaser' => 'purchaser',
            'pr-gm-factory' => 'gm',
            default => null,
        };
    }

    /**
     * Get a human-readable name of who is/was the approver.
     */
    public function getApproverNameAttribute(): string
    {
        if ($this->approver_snapshot_name) {
            return $this->approver_snapshot_name;
        }

        if ($this->approver_type === 'user') {
            return \App\Infrastructure\Persistence\Eloquent\Models\User::find($this->approver_id)?->name ?? 'Unknown User';
        }

        // role fallback
        return \Spatie\Permission\Models\Role::find($this->approver_id)?->name ?? 'Unknown Role';
    }

    /**
     * Get a human-readable label of the role.
     */
    public function getApproverLabelAttribute(): string
    {
        if ($this->approver_snapshot_label) {
            return $this->approver_snapshot_label;
        }

        // Use the mapping logic from ApprovalEngine for fallback if needed, 
        // but for now just return the name/type
        return $this->approver_snapshot_name ?? $this->approver_type;
    }
}
