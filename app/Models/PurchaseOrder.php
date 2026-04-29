<?php

namespace App\Models;

use App\Domain\Approval\Contracts\Approvable;
use App\Enums\PurchaseOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class PurchaseOrder extends Model implements Approvable
{
    use HasFactory;

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
        'status', // Legacy field - use workflow status instead
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

    /**
     * Get the current status enum based on workflow status
     */
    public function getStatusEnum(): PurchaseOrderStatus
    {
        return PurchaseOrderStatus::fromWorkflowStatus($this->workflow_status);
    }

    /**
     * Check if PO can be edited (only rejected/cancelled can be revised)
     */
    public function canBeEdited(): bool
    {
        $status = $this->getStatusEnum();
        return in_array($status, [PurchaseOrderStatus::REJECTED, PurchaseOrderStatus::CANCELLED]);
    }

    /**
     * Check if PO is in a terminal state (cannot be changed further)
     */
    public function isTerminal(): bool
    {
        return $this->getStatusEnum()->isTerminal();
    }

    /**
     * Scope: Filter by workflow status
     */
    public function scopeWithWorkflowStatus($query, string $workflowStatus)
    {
        return $query->whereHas('approvalRequest', function ($q) use ($workflowStatus) {
            $q->where('status', $workflowStatus);
        })->orWhere(function ($q) use ($workflowStatus) {
            // Include POs without approval requests for DRAFT status
            if ($workflowStatus === 'DRAFT') {
                $q->whereNull('approval_request_id');
            }
        });
    }

    /**
     * Scope: Filter editable POs (can be revised)
     */
    public function scopeEditable($query)
    {
        return $query->whereHas('approvalRequest', function ($q) {
            $q->whereIn('status', ['REJECTED', 'CANCELLED']);
        });
    }

    /**
     * Scope: Filter POs approved in current month
     */
    public function scopeApprovedThisMonth($query)
    {
        return $query->whereHas('approvalRequest', function ($q) {
            $q->where('status', 'APPROVED')
              ->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()]);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function latestDownloadLog()
    {
        return $this->hasOne(PurchaseOrderDownloadLog::class)->latestOfMany();
    }

    public function category()
    {
        return $this->belongsTo(PurchaseOrderCategory::class, 'purchase_order_category_id');
    }

    // === APPROVAL SYSTEM INTEGRATION ===

    public function approvalRequest(): MorphOne
    {
        return $this->morphOne(\App\Infrastructure\Persistence\Eloquent\Models\ApprovalRequest::class, 'approvable');
    }

    public function getApprovableTypeLabel(): string
    {
        return 'Purchase Order';
    }

    public function getApprovableIdentifier(): string
    {
        return "PO/{$this->po_number}";
    }

    public function getApprovableShowUrl(): string
    {
        return route('po.show', $this->id);
    }

    public function getApprovableDepartmentName(): ?string
    {
        return $this->user?->department?->name;
    }

    public function getApprovableBranchValue(): ?string
    {
        // Return branch information if available
        return $this->user?->branch?->name;
    }

    /**
     * Get workflow status from approval request.
     * This replaces the legacy status column.
     */
    public function getWorkflowStatusAttribute(): ?string
    {
        // Return status from approval request, or DRAFT if no approval
        return $this->approvalRequest?->status ?? 'DRAFT';
    }

    /**
     * Get current workflow step (approver label).
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
