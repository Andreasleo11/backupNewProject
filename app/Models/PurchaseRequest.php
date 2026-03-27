<?php

namespace App\Models;

use App\Domain\Approval\Contracts\Approvable;
use App\Enums\ToDepartment;
use App\Infrastructure\Approval\Concerns\HasApproval;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseRequest extends Model implements Approvable
{
    use HasApproval, HasFactory, LogsActivity, SoftDeletes;

    /**
     * Get combined activities from PR and its Items.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCombinedActivitiesAttribute()
    {
        // 1. Get PR Logs
        $prLogs = $this->activities;

        // 2. Get Item Logs
        $itemIds = $this->items()->pluck('id');

        $itemLogs = \Spatie\Activitylog\Models\Activity::where('subject_type', \App\Models\DetailPurchaseRequest::class)
            ->whereIn('subject_id', $itemIds)
            ->with('causer')
            ->get();

        // 3. Get File Logs (New)
        // Assuming files are linked via doc_num. We need to find File IDs first.
        // Since there is no direct relationship defined yet (files() accessor might exist but using raw query for safety)
        $fileIds = \App\Models\File::where('doc_id', $this->doc_num)->pluck('id');

        $fileLogs = \Spatie\Activitylog\Models\Activity::where('subject_type', \App\Models\File::class)
            ->whereIn('subject_id', $fileIds)
            ->with('causer')
            ->get();

        // 4. Get Approval Actions
        $approvalActions = collect();
        if ($this->approvalRequest) {
            $approvalActions = $this->approvalRequest->actions()
                ->with('causer')
                ->get();
        }

        // 5. Merge & Sort desc
        return $prLogs->concat($itemLogs)
            ->concat($fileLogs)
            ->concat($approvalActions)
            ->sortByDesc('created_at');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'user_id_create',
        'date_pr',
        'date_required',
        'remark',
        'to_department',
        'pr_no',
        'supplier',
        'pic',
        'type',
        'from_department',
        'is_import',
        'is_cancel',
        'po_number',
        'doc_num',
        'branch',
    ];

    protected $casts = [
        'to_department' => ToDepartment::class,
        'branch' => \App\Enums\Branch::class,
    ];

    public function items()
    {
        // sesuaikan, kalau sudah pakai PurchaseRequestItem ganti di sini
        return $this->hasMany(DetailPurchaseRequest::class);
    }

    public function fromDepartment()
    {
        return $this->belongsTo(Department::class, 'from_department', 'name');
        // atau 'from_department_id' kalau sudah dinormalisasi
    }

    public function signatures()
    {
        return $this->hasMany(PurchaseRequestSignature::class);
    }

    /**
     * Get all signatures, merging modern approval steps, legacy signatures relationship, and legacy columns.
     * Useful during the transition period.
     */
    public function getAllSignaturesAttribute(): array
    {
        $signatures = collect();

        if ($this->approvalRequest) {
            $approvalSignatures = $this->approvalRequest->steps()
                ->whereNotNull('acted_at')
                ->whereNotNull('signature_image_path')
                ->with('actedUser')
                ->get()
                ->map(function ($step) {
                    return [
                        'step_code' => $step->approver_label ?? 'Approver',
                        'user' => $step->actedUser,
                        'name' => $step->approver_name ?? 'Unknown',
                        'image' => $step->signature_image_path,
                        'at' => $step->acted_at,
                        'source' => 'approval_system',
                    ];
                });
            $signatures = $signatures->merge($approvalSignatures);
        }

        return $signatures->unique(function ($item) {
            return $item['step_code'] . $item['name'];
        })->values()->toArray();
    }

    /**
     * Get all workflow steps for visualization, including pending and empty slots.
     * Prepend the creator's signature manually as requested.
     */
    public function getWorkflowSignaturesAttribute(): array
    {
        $signatures = [];

        // 1. Manually add Creator (MAKER) at the start
        $creator = $this->creator;
        if ($creator) {
            $signatures[] = [
                'step_code'  => 'Prepared By',
                'user'       => $creator,
                'name'       => $creator->name,
                'image'      => $this->creator_signature_url,
                'at'         => $this->created_at,
                'status'     => 'signed',
                'is_current' => false,
                'source'     => 'creator',
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

    /**
     * Helper to get signature URL for the creator.
     */
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

    public function itemDetail()
    {
        return $this->hasMany(DetailPurchaseRequest::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'user_id_create');
    }

    /**
     * Alias for createdBy() for better semantic clarity
     */
    public function creator()
    {
        return $this->createdBy();
    }

    public function files()
    {
        return $this->hasMany(File::class, 'doc_id', 'doc_num');
    }

    /**
     * Reset item-level approvals when PR is returned for revision.
     */
    public function resetItemApprovals(): void
    {
        $this->itemDetail()->update([
            'is_approve_by_head' => null,
            'is_approve_by_gm' => null,
            'is_approve_by_verificator' => null,
            'is_approve' => null,
        ]);
    }

    /**
     * Get workflow status from approval request.
     * This replaces the workflow_status column.
     */
    public function getWorkflowStatusAttribute(): ?string
    {
        // If cancelled, return CANCELED
        if ((int) $this->is_cancel === 1) {
            return 'CANCELED';
        }

        // Return status from approval request, or DRAFT if no approval
        return $this->approvalRequest?->status ?? 'DRAFT';
    }

    /**
     * Get current workflow step (approver label).
     * This replaces the workflow_step column.
     */
    public function getWorkflowStepAttribute(): ?string
    {
        $approval = $this->approvalRequest;

        if (! $approval || $approval->status !== 'IN_REVIEW') {
            return null;
        }

        $currentStep = $approval->steps()
            ->where('sequence', $approval->current_step)
            ->first();

        return $currentStep?->approver_snapshot_label ?? $currentStep?->approver_label;
    }

    /**
     * Get current approver name (alias for workflow_step).
     */
    public function getCurrentApproverAttribute(): ?string
    {
        return $this->workflow_step;
    }

    /**
     * Scope: Filter PRs that are in review.
     */
    public function scopeInReview(Builder $query): Builder
    {
        return $query->whereHas(
            'approvalRequest',
            fn ($q) => $q->where('status', 'IN_REVIEW')
        );
    }

    /**
     * Scope: Filter PRs that are approved by workflow.
     */
    public function scopeWorkflowApproved(Builder $query): Builder
    {
        return $query->whereHas(
            'approvalRequest',
            fn ($q) => $q->where('status', 'APPROVED')
        );
    }

    /**
     * Scope: Filter PRs that are rejected by workflow.
     */
    public function scopeWorkflowRejected(Builder $query): Builder
    {
        return $query->whereHas(
            'approvalRequest',
            fn ($q) => $q->where('status', 'REJECTED')
        );
    }

    /**
     * Scope: Filter PRs that are cancelled.
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('is_cancel', 1);
    }

    // --- Approvable Interface ---

    public function getApprovableTypeLabel(): string
    {
        return 'Purchase Request';
    }

    public function getApprovableIdentifier(): string
    {
        return $this->pr_no ?? (string)$this->doc_num;
    }

    public function getApprovableShowUrl(): string
    {
        return route('purchase-requests.show', $this->id);
    }

    public function getApprovableDepartmentName(): ?string
    {
        return (string) $this->from_department;
    }

    public function getApprovableBranchValue(): ?string
    {
        return $this->branch?->value;
    }
}
