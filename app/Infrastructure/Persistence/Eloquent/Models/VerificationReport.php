<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Domain\Approval\Contracts\Approvable;
use App\Infrastructure\Approval\Concerns\HasApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VerificationReport extends Model implements Approvable
{
    use HasApproval;

    protected $table = 'verification_reports';

    protected $fillable = [
        'document_number', 'creator_id', 'status', 'meta',
        'rec_date', 'verify_date', 'customer', 'invoice_number',
    ];

    protected $casts = [
        'meta' => 'array',
        'rec_date' => 'date',
        'verify_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(VerificationItem::class, 'verification_report_id');
    }

    public function getApprovalStatusAttribute(): string
    {
        return $this->approvalStatus(); // DRAFT | IN_REVIEW | APPROVED | REJECTED
    }
}
