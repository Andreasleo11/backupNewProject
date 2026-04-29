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

    protected $fillable = [
        'po_number',
        'approved_date',
        'status',
        'filename',
        'reason',
        'creator_id',
        'downloaded_at',
        'vendor_name',
        'invoice_date',
        'currency',
        'total',
        'tanggal_pembayaran',
        'invoice_number',
        'purchase_order_category_id',
        'parent_po_number',
        'revision_count',
        'approval_request_id',
    ];

    // Enum-based status methods
    public function getStatusEnum(): PurchaseOrderStatus
    {
        return PurchaseOrderStatus::fromLegacyValue($this->status);
    }

    public function setStatusEnum(PurchaseOrderStatus $status): void
    {
        $this->status = $status->legacyValue();
    }

    public function isStatus(PurchaseOrderStatus $status): bool
    {
        return $this->status === $status->legacyValue();
    }

    public function canTransitionTo(PurchaseOrderStatus $newStatus): bool
    {
        $currentStatus = $this->getStatusEnum();

        return match ($currentStatus) {
            PurchaseOrderStatus::PENDING_APPROVAL => in_array($newStatus, [PurchaseOrderStatus::APPROVED, PurchaseOrderStatus::REJECTED, PurchaseOrderStatus::CANCELLED]),
            PurchaseOrderStatus::REJECTED, PurchaseOrderStatus::CANCELLED => in_array($newStatus, [PurchaseOrderStatus::PENDING_APPROVAL]), // Can resubmit
            PurchaseOrderStatus::APPROVED, PurchaseOrderStatus::DRAFT => false, // Terminal states
        };
    }

    // Legacy scope methods (keeping for backward compatibility)
    public function scopeApproved($query)
    {
        return $query->where('status', PurchaseOrderStatus::APPROVED->legacyValue());
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', PurchaseOrderStatus::PENDING_APPROVAL->legacyValue());
    }

    public function scopeWaiting($query) // Backward compatibility
    {
        return $this->scopePendingApproval($query);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', PurchaseOrderStatus::REJECTED->legacyValue());
    }

    public function scopeCanceled($query)
    {
        return $query->where('status', PurchaseOrderStatus::CANCELLED->legacyValue());
    }

    // New enum-based scopes
    public function scopeWithStatus($query, PurchaseOrderStatus $status)
    {
        return $query->where('status', $status->legacyValue());
    }

    public function scopeDraft($query)
    {
        return $query->where('status', PurchaseOrderStatus::DRAFT->legacyValue());
    }

    public function scopeTerminal($query)
    {
        return $query->whereIn('status', [
            PurchaseOrderStatus::APPROVED->legacyValue(),
            PurchaseOrderStatus::REJECTED->legacyValue(),
            PurchaseOrderStatus::CANCELLED->legacyValue(),
        ]);
    }

    public function scopeEditable($query)
    {
        return $query->whereIn('status', [
            PurchaseOrderStatus::REJECTED->legacyValue(),
            PurchaseOrderStatus::CANCELLED->legacyValue(),
        ]);
    }

    public function scopeApprovedForCurrentMonth($query)
    {
        return $query
            ->where('status', 2)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
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

    public function getVendorNames()
    {
        $vendorNames = Vendor::pluck('name');

        return response()->json([
            'vendorNames' => $vendorNames,
        ]);
    }
}
