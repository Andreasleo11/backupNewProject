<?php

declare(strict_types=1);

namespace App\Domain\PurchaseRequest\ValueObjects;

/**
 * Value Object representing a Purchase Request workflow step.
 * Encapsulates business rules about workflow progression.
 */
final class WorkflowStep
{
    private function __construct(
        private readonly int $value,
        private readonly string $name,
        private readonly ?string $roleSlug
    ) {}

    public static function deptHead(): self
    {
        return new self(1, 'Department Head Review', 'pr-dept-head');
    }

    public static function verificator(): self
    {
        return new self(2, 'Verificator Review', 'pr-verificator');
    }

    public static function director(): self
    {
        return new self(3, 'Director Approval', 'pr-director');
    }

    public static function accounting(): self
    {
        return new self(4, 'Accounting Processing', 'pr-accounting');
    }

    public static function completed(): self
    {
        return new self(5, 'Completed', null);
    }

    public static function fromValue(int $value): self
    {
        return match ($value) {
            1 => self::deptHead(),
            2 => self::verificator(),
            3 => self::director(),
            4 => self::accounting(),
            5 => self::completed(),
            default => throw new \DomainException("Invalid workflow step: {$value}"),
        };
    }

    public static function fromRoleSlug(string $roleSlug): ?self
    {
        // Handle variations in role slugs
        return match (true) {
            str_contains($roleSlug, 'dept-head') => self::deptHead(),
            str_contains($roleSlug, 'verificator') => self::verificator(),
            str_contains($roleSlug, 'director') => self::director(),
            str_contains($roleSlug, 'accounting') => self::accounting(),
            default => null,
        };
    }

    public function value(): int
    {
        return $this->value;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function roleSlug(): ?string
    {
        return $this->roleSlug;
    }

    /**
     * Get the approver type for item-level approvals.
     */
    public function approverType(): ?string
    {
        return match ($this->value) {
            1 => 'head',
            2 => 'verificator',
            3 => 'director',
            default => null,
        };
    }

    /**
     * Check if this step requires item-level approval.
     */
    public function requiresItemApproval(): bool
    {
        return in_array($this->value, [1, 2, 3]);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
