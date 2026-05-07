<?php

namespace App\Models;

use App\Domain\Approval\Contracts\Approvable;
use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseOrder extends Model implements Approvable
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $casts = [
        'invoice_date' => 'date',
        'approved_date' => 'datetime',
        'tanggal_pembayaran' => 'date',
        'downloaded_at' => 'datetime',
        'total' => 'decimal:2',
        'revision_count' => 'integer',
    ];

    protected $fillable = [
        'po_number',
        'approved_date',
        'filename',
        'reason',
        'creator_id',
        'downloaded_at',
        'vendor_name',
        'invoice_date',
        'invoice_number',
        'currency',
        'total',
        'tanggal_pembayaran',
        'purchase_order_category_id',
        'parent_po_number',
        'revision_count',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // =========================================================================
    // Relationships
    // =========================================================================

    public function user()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function category()
    {
        return $this->belongsTo(PurchaseOrderCategory::class, 'purchase_order_category_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function latestDownloadLog()
    {
        return $this->hasOne(PurchaseOrderDownloadLog::class)->latestOfMany();
    }

    public function downloadLogs()
    {
        return $this->hasMany(PurchaseOrderDownloadLog::class)->latest();
    }

    // =========================================================================
    // Status helpers
    // =========================================================================

    /**
     * Get the current status enum based on workflow status.
     */
    public function getStatusEnum(): PurchaseOrderStatus
    {
        return PurchaseOrderStatus::fromWorkflowStatus($this->workflow_status);
    }

    // =========================================================================
    // Query Scopes
    // =========================================================================

    /**
     * Scope: Filter by workflow status.
     */
    public function scopeWithWorkflowStatus($query, string $workflowStatus)
    {
        if ($workflowStatus === 'DRAFT') {
            return $query->whereDoesntHave('approvalRequest')
                ->orWhereHas('approvalRequest', function ($q) {
                    $q->where('status', 'DRAFT');
                });
        }

        return $query->whereHas('approvalRequest', function ($q) use ($workflowStatus) {
            $q->where('status', $workflowStatus);
        });
    }

    /**
     * Scope: Filter editable POs (can be revised).
     */
    public function scopeEditable($query)
    {
        return $query->whereHas('approvalRequest', function ($q) {
            $q->whereIn('status', ['REJECTED', 'CANCELLED']);
        });
    }

    /**
     * Scope: Filter POs approved in current month.
     */
    public function scopeApprovedThisMonth($query)
    {
        return $query->whereHas('approvalRequest', function ($q) {
            $q->where('status', 'APPROVED')
                ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()]);
        });
    }

    // =========================================================================
    // Approval System Integration (Approvable contract)
    // =========================================================================

    public function approvalRequest(): MorphOne
    {
        return $this->morphOne(
            \App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest::class,
            'approvable'
        );
    }

    public function getApprovableTypeLabel(): string
    {
        return 'Purchase Order';
    }

    public function getApprovableIdentifier(): string
    {
        return $this->id;
    }

    public function getApprovableShowUrl(): string
    {
        return route('po.view', $this->id);
    }

    public function getApprovableDepartmentName(): ?string
    {
        return $this->user?->department?->name;
    }

    public function getApprovableBranchValue(): ?string
    {
        return $this->user?->branch?->name;
    }

    // =========================================================================
    // Dynamic / Computed Attributes
    // =========================================================================

    /**
     * Get workflow status from approval request.
     * This replaces the legacy status column.
     */
    public function getWorkflowStatusAttribute(): ?string
    {
        if (! $this->relationLoaded('approvalRequest')) {
            $this->load('approvalRequest:id,approvable_id,approvable_type,status');
        }

        return $this->approvalRequest?->status ?? 'DRAFT';
    }

    /**
     * Get current workflow step label (approver label).
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
}
