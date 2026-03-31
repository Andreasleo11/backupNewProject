<?php

namespace App\Domain\Overtime\Models;

use App\Domain\Approval\Contracts\Approvable;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\ApprovalFlow;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OvertimeForm extends Model implements Approvable
{
    use SoftDeletes, HasFactory;

    protected $table = 'header_form_overtime';

    protected $fillable = [
        'user_id',
        'dept_id',
        'branch',
        'status',
        'is_design',
        'is_export',
        'description',
        'is_planned',
        'approval_flow_id',
        'is_after_hour',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function department()
    {
        return $this->hasOne(\App\Infrastructure\Persistence\Eloquent\Models\Department::class, 'id', 'dept_id');
    }

    public function details()
    {
        return $this->hasMany(OvertimeFormDetail::class, 'header_id', 'id');
    }

    public function processedDetails()
    {
        return $this->hasMany(OvertimeFormDetail::class, 'header_id', 'id')
                    ->where('status', 'Approved')
                    ->where('is_processed', 1);
    }

    public function failedDetails()
    {
        return $this->hasMany(OvertimeFormDetail::class, 'header_id', 'id')
                    ->where('status', 'Rejected')
                    ->where('reason', 'like', '%JPAYROLL%');
    }

    public function flow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id', 'id');
    }

    public function approvals()
    {
        return $this->hasMany(\App\Models\OvertimeFormApproval::class, 'overtime_form_id', 'id');
    }



    // -------------------------------------------------------------------------
    // Approvable contract — makes Overtime polymorphically compatible with the
    // unified approval UI used by PurchaseRequest and IT Ticket.
    // -------------------------------------------------------------------------

    /**
     * MorphOne relation to the unified Approval Engine.
     */
    public function approvalRequest()
    {
        return $this->morphOne(\App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest::class, 'approvable');
    }

    public function getApprovableTypeLabel(): string
    {
        return 'Form Overtime';
    }

    public function getApprovableIdentifier(): string
    {
        return 'OT-' . $this->id;
    }

    public function getApprovableShowUrl(): string
    {
        return route('overtime.detail', $this->id);
    }

    public function getApprovableDepartmentName(): ?string
    {
        return $this->department?->name;
    }

    public function getApprovableBranchValue(): ?string
    {
        return (string) $this->branch;
    }

    /**
     * Get workflow status from approval request.
     * This replaces the legacy status column.
     */
    public function getWorkflowStatusAttribute(): string
    {
        return $this->approvalRequest?->status ?? 'DRAFT';
    }

    /**
     * Get current workflow step (approver label).
     */
    public function getWorkflowStepAttribute(): ?string
    {
        $approval = $this->approvalRequest;

        if (!$approval || $approval->status !== 'IN_REVIEW') {
            return null;
        }

        $currentStep = $approval->steps()
            ->where('sequence', $approval->current_step)
            ->first();

        return $currentStep?->approver_snapshot_label ?? $currentStep?->approver_label;
    }

    /**
     * Scope: Filter forms that are in review.
     */
    public function scopeInReview($query)
    {
        return $query->whereHas(
            'approvalRequest',
            fn ($q) => $q->where('status', 'IN_REVIEW')
        );
    }

    /**
     * Scope: Filter forms that are approved by workflow.
     */
    public function scopeWorkflowApproved($query)
    {
        return $query->whereHas(
            'approvalRequest',
            fn ($q) => $q->where('status', 'APPROVED')
        );
    }

    /**
     * Scope: Filter forms that are rejected by workflow.
     */
    public function scopeWorkflowRejected($query)
    {
        return $query->whereHas(
            'approvalRequest',
            fn ($q) => $q->where('status', 'REJECTED')
        );
    }

    /**
     * Centralized query scope for role-based visibility.
     * Delegates to the unified Approval Request scoper.
     */
    public function scopeByRole($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            // 1. Super-admin or specialized view-all sees everything
            if ($user->hasRole('super-admin') || $user->can('overtime.view-all')) {
                return;
            }

            // 2. Creator always sees their own
            $q->where('user_id', $user->id);

            // 3. Everything else: Use Centralized Approval Scoper 
            $q->orWhereHas('approvalRequest', function ($aq) use ($user) {
                $aq->forUser($user);
            });
        });
    }
}

