<?php

namespace App\Enums;

enum PurchaseOrderStatus: int
{
    case DRAFT = 1;
    case WAITING = 2;
    case APPROVED = 3;
    case REJECTED = 4;
    case CANCELLED = 5;

    /**
     * Get the human-readable label for the status
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::WAITING => 'Waiting for Approval',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::CANCELLED => 'Cancelled',
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
            self::DRAFT, self::REJECTED => true,
            self::WAITING, self::APPROVED, self::CANCELLED => false,
        };
    }

    /**
     * Check if the status allows approval actions
     */
    public function canApprove(): bool
    {
        return $this === self::WAITING;
    }

    /**
     * Check if the status allows rejection actions
     */
    public function canReject(): bool
    {
        return $this === self::WAITING;
    }

    /**
     * Check if the status is terminal (no further actions possible)
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::APPROVED, self::REJECTED, self::CANCELLED => true,
            self::DRAFT, self::WAITING => false,
        };
    }

    /**
     * Get the CSS class for UI display
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-gray-100 text-gray-800',
            self::WAITING => 'bg-yellow-100 text-yellow-800',
            self::APPROVED => 'bg-green-100 text-green-800',
            self::REJECTED => 'bg-red-100 text-red-800',
            self::CANCELLED => 'bg-orange-100 text-orange-800',
        };
    }

    /**
     * Create enum instance from legacy integer value
     */
    public static function fromLegacyValue(int $value): self
    {
        return match ($value) {
            1 => self::DRAFT,
            2 => self::WAITING,
            3 => self::APPROVED,
            4 => self::REJECTED,
            5 => self::CANCELLED,
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
