<?php

namespace App\Domain\User\Entities;

use App\Domain\User\ValueObjects\Email;

class User
{
    public function __construct(
        private ?int $id,
        private string $name,
        private Email $email,
        private bool $active = true,
        private array $roles = [],
        private ?int $employeeId = null,
    ) {}

    public function id(): ?int       { return $this->id; }
    public function name(): string   { return $this->name; }
    public function email(): Email   { return $this->email; }
    public function isActive(): bool { return $this->active; }
    public function roles(): array   { return $this->roles; }
    public function employeeId(): ?int { return $this->employeeId; }

    public function rename(string $name): void { $this->name = $name; }
    public function activate(): void   { $this->active = true; }
    public function deactivate(): void { $this->active = false; }
    public function setRoles(array $roles): void { $this->roles = $roles; }
    public function changeEmail(Email $email): void { $this->email = $email; }
    public function setEmployeeId(int $employeeId): void { $this->employeeId = $employeeId; }

    // If you want a convenient "copy" method:
    public function with(
        ?string $name = null,
        ?Email $email = null,
        ?bool $active = null,
        ?array $roles = null,
        ?int $employeeId = null,
    ): self {
        return new self(
            id: $this->id,
            name: $name ?? $this->name,
            email: $email ?? $this->email,
            active: $active ?? $this->active,
            roles: $roles ?? $this->roles,
            employeeId: $employeeId ?? $this->employeeId,
        );
    }
}
