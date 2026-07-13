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

    public function files(): HasMany
    {
        return $this->hasMany(\App\Models\File::class, 'doc_id', 'document_number');
    }


    public function getApprovableTypeLabel(): string
    {
        return 'Verification Report';
    }

    public function getApprovableIdentifier(): string
    {
        return $this->document_number ?? (string) $this->getKey();
    }

    public function getApprovableShowUrl(): string
    {
        return route('verification.show', $this->id);
    }

    public function getApprovableDepartmentName(): ?string
    {
        return data_get($this->meta, 'department');
    }

    public function getApprovableBranchValue(): ?string
    {
        return null; // ponytail: no branch concept on verification reports
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function getWorkflowStatusAttribute(): string
    {
        return $this->status;
    }

    public function getIsCancelAttribute(): bool
    {
        return false;
    }

    public function getCreatorSignatureUrlAttribute(): ?string
    {
        $defaultSig = $this->creator?->signatures()
            ->whereNull('revoked_at')
            ->orderByDesc('is_default')
            ->first();

        if ($defaultSig) {
            return route('signatures.show', ['id' => $defaultSig->id]);
        }

        return null;
    }

    public function getWorkflowSignaturesAttribute(): array
    {
        $signatures = [];

        // 1. Manually add Creator (MAKER) at the start
        $creator = $this->creator;
        if ($creator) {
            $signatures[] = [
                'step_code' => 'Prepared By',
                'user' => $creator,
                'name' => $creator->name,
                'image' => $this->creator_signature_url,
                'at' => $this->created_at,
                'status' => 'signed',
                'is_current' => false,
                'source' => 'creator',
            ];
        }

        if ($this->approvalRequest) {
            $approvalSteps = $this->approvalRequest->steps()
                ->orderBy('sequence')
                ->with('actedUser')
                ->get()
                ->map(function ($step) {
                    $status = strtoupper($step->status);

                    $uiStatus = match ($status) {
                        'APPROVED' => 'signed',
                        'REJECTED' => 'rejected',
                        'PENDING', 'IN_PROGRESS' => 'pending',
                        default => 'pending',
                    };

                    if ($step->acted_at && $step->signature_image_path) {
                        $uiStatus = 'signed';
                    }

                    return [
                        'step_code' => $step->approver_label ?? 'Approver',
                        'user' => $step->actedUser,
                        'name' => $step->approver_name ?? 'Waiting...',
                        'image' => $step->signature_url,
                        'at' => $step->acted_at,
                        'status' => $uiStatus,
                        'is_current' => $this->approvalRequest->current_step == $step->sequence && $this->approvalRequest->status === 'IN_REVIEW',
                        'source' => 'approval_system',
                    ];
                });

            $signatures = array_merge($signatures, $approvalSteps->toArray());
        }

        return $signatures;
    }
}
