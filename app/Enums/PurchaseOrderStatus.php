<?php

namespace App\Enums;

enum PurchaseOrderStatus: int
{
    case PENDING_APPROVAL = 1;  // Waiting for director approval
    case APPROVED = 2;          // Director has approved and signed
    case REJECTED = 3;
    case CANCELLED = 4;
    case DRAFT = 5;             // Legacy draft status (if needed)

    /**
     * Get the human-readable label for the status
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING_APPROVAL => 'Pending Approval',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::CANCELLED => 'Cancelled',
            self::DRAFT => 'Draft',
        };
    }

    /**
     * Get the legacy integer value for backward compatibility
     */
    public function legacyValue(): int
    {
        return $this->value;
    }

    /**
     * Check if the status allows editing
     */
    public function canEdit(): bool
    {
        return match ($this) {
            self::DRAFT, self::REJECTED, self::CANCELLED => true,  // Can edit draft, rejected, or cancelled for revisions
            self::PENDING_APPROVAL, self::APPROVED => false,
        };
    }

    /**
     * Check if the status allows approval actions
     */
    public function canApprove(): bool
    {
        return $this === self::PENDING_APPROVAL;
    }

    /**
     * Check if the status allows rejection actions
     */
    public function canReject(): bool
    {
        return $this === self::PENDING_APPROVAL;
    }

    /**
     * Check if the status is pending approval
     */
    public function isPendingApproval(): bool
    {
        return $this === self::PENDING_APPROVAL;
    }

    /**
     * Check if the status is terminal (no further actions possible)
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::APPROVED, self::REJECTED, self::CANCELLED => true,
            self::PENDING_APPROVAL, self::DRAFT => false,
        };
    }

    /**
     * Get the CSS class for UI display
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::PENDING_APPROVAL => 'bg-amber-100 text-amber-800 border-amber-200',
            self::APPROVED => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            self::REJECTED => 'bg-rose-100 text-rose-800 border-rose-200',
            self::CANCELLED => 'bg-orange-100 text-orange-800 border-orange-200',
            self::DRAFT => 'bg-slate-100 text-slate-800 border-slate-200',
        };
    }

    /**
     * Get all possible status values for validation
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get all status options for dropdowns/forms
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /**
     * Create enum instance from workflow status string
     * Maps approval workflow statuses to PO statuses
     */
    public static function fromWorkflowStatus(string $workflowStatus): self
    {
        return match (strtoupper($workflowStatus)) {
            'DRAFT' => self::DRAFT,
            'IN_REVIEW' => self::PENDING_APPROVAL,
            'APPROVED' => self::APPROVED,
            'REJECTED' => self::REJECTED,
            'CANCELLED', 'RETURNED' => self::CANCELLED,
            default => throw new \InvalidArgumentException("Invalid workflow status: {$workflowStatus}"),
        };
    }

    /**
     * Get workflow status string representation
     */
    public function toWorkflowStatus(): string
    {
        return match ($this) {
            self::DRAFT => 'DRAFT',
            self::PENDING_APPROVAL => 'IN_REVIEW',
            self::APPROVED => 'APPROVED',
            self::REJECTED => 'REJECTED',
            self::CANCELLED => 'CANCELLED',
        };
    }

    /**
     * Get description for status
     */
    public function description(): string
    {
        return match ($this) {
            self::PENDING_APPROVAL => 'Waiting for director approval',
            self::APPROVED => 'Approved and ready for processing',
            self::REJECTED => 'Rejected by approver',
            self::CANCELLED => 'Cancelled or returned',
            self::DRAFT => 'Draft - not yet submitted',
        };
    }
}
