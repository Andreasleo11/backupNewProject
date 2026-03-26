<?php

namespace App\Domain\PurchaseRequest\DTOs;

/**
 * Data Transfer Object for validation results.
 * Used to return validation status with messages and details.
 */
class ValidationResult
{
    public function __construct(
        private bool $isValid,
        private string $message,
        private array $details = []
    ) {}

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * Create a successful validation result.
     */
    public static function success(string $message = 'Validation passed'): self
    {
        return new self(true, $message);
    }

    /**
     * Create a failed validation result.
     */
    public static function failure(string $message, array $details = []): self
    {
        return new self(false, $message, $details);
    }
}
