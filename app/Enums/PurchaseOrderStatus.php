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
            self::REJECTED, self::CANCELLED => true,  // Can edit rejected/cancelled for revisions
            self::PENDING_APPROVAL, self::APPROVED, self::DRAFT => false,
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
            self::PENDING_APPROVAL => 'bg-yellow-100 text-yellow-800',
            self::APPROVED => 'bg-green-100 text-green-800',
            self::REJECTED => 'bg-red-100 text-red-800',
            self::CANCELLED => 'bg-orange-100 text-orange-800',
            self::DRAFT => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Create enum instance from legacy integer value
     */
    public static function fromLegacyValue(int $value): self
    {
        return match ($value) {
            1 => self::PENDING_APPROVAL,
            2 => self::APPROVED,
            3 => self::REJECTED,
            4 => self::CANCELLED,
            5 => self::DRAFT,
            default => throw new \InvalidArgumentException("Invalid status value: {$value}"),
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
}
