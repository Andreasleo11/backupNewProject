<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Infrastructure\Approval\Concerns\HasApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VerificationReport extends Model
{
    use HasApproval;

    protected $table = 'verification_reports';

    protected $fillable = ['document_number', 'creator_id', 'title', 'description', 'status', 'meta'];

    protected $casts = ['meta' => 'array'];

    public function items(): HasMany
    {
        return $this->hasMany(VerificationItem::class, 'verification_report_id');
    }

    public function getApprovalStatusAttribute(): string
    {
        return $this->approvalStatus(); // DRAFT | IN_REVIEW | APPROVED | REJECTED
    }
}
