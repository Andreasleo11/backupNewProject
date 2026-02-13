<?php

declare(strict_types=1);

namespace App\Domain\PurchaseRequest\ValueObjects;

/**
 * Value Object representing a Purchase Request workflow step.
 * 
 * **Purpose:**
 * - Provides type-safe workflow step representation
 * - Maps role slugs to approver types for item-level approvals
 * - Encapsulates business rules about workflow progression
 * - Single source of truth for step identifiers
 * 
 * **Use Cases:**
 * - Determining which database column to update for item approvals
 * - Checking if a step requires item-level review
 * - Mapping approval steps to human-readable names
 */
final class WorkflowStep
{
    // Step identifiers (constants eliminate magic numbers)
    private const DEPT_HEAD = 1;
    private const GM = 2;
    private const VERIFICATOR = 3;
    private const DIRECTOR = 4;
    private const ACCOUNTING = 5;
    private const COMPLETED = 6;
    
    // Steps that require item-level approval
    private const ITEM_APPROVAL_STEPS = [
        self::DEPT_HEAD,
        self::GM,
        self::VERIFICATOR,
        self::DIRECTOR,
    ];

    private function __construct(
        private readonly int $value,
        private readonly string $name,
        private readonly ?string $roleSlug
    ) {}

    public static function deptHead(): self
    {
        return new self(self::DEPT_HEAD, 'Department Head Review', 'pr-dept-head');
    }
    
    public static function gm(): self
    {
        return new self(self::GM, 'GM Review', 'pr-gm');
    }

    public static function verificator(): self
    {
        return new self(self::VERIFICATOR, 'Verificator Review', 'pr-verificator');
    }

    public static function director(): self
    {
        return new self(self::DIRECTOR, 'Director Approval', 'pr-director');
    }

    public static function accounting(): self
    {
        return new self(self::ACCOUNTING, 'Accounting Processing', 'pr-accounting');
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED, 'Completed', null);
    }

    public static function fromValue(int $value): self
    {
        return match ($value) {
            self::DEPT_HEAD => self::deptHead(),
            self::GM => self::gm(),
            self::VERIFICATOR => self::verificator(),
            self::DIRECTOR => self::director(),
            self::ACCOUNTING => self::accounting(),
            self::COMPLETED => self::completed(),
            default => throw new \DomainException("Invalid workflow step: {$value}"),
        };
    }

    public static function fromRoleSlug(string $roleSlug): ?self
    {
        // Handle variations in role slugs
        return match (true) {
            str_contains($roleSlug, 'dept-head') => self::deptHead(),
            str_contains($roleSlug, 'gm') => self::gm(),
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
     * This maps to the database column prefix (e.g., 'head' => 'is_approve_by_head').
     * 
     * @return string|null The approver type, or null if this step doesn't approve items
     */
    public function approverType(): ?string
    {
        return match ($this->value) {
            self::DEPT_HEAD => 'head',
            self::GM => 'gm',
            self::VERIFICATOR => 'verificator',
            self::DIRECTOR => 'director',
            default => null,
        };
    }

    /**
     * Check if this step requires item-level approval.
     * 
     * **Item Approval Policy:**
     * - Purchaser and Accounting can approve PRs directly without reviewing items
     * - All other roles (Head, GM, Verificator, Director) must review items
     * 
     * @return bool True if this step requires item approvals before PR approval
     */
    public function requiresItemApproval(): bool
    {
        // Roles that can skip item approval
        $skipItemApproval = ['pr-purchaser', 'pr-accounting'];
        
        if (in_array($this->roleSlug, $skipItemApproval)) {
            return false;
        }
        
        // All other configured steps require item approval
        return in_array($this->value, self::ITEM_APPROVAL_STEPS);
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
