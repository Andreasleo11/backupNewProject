<?php

namespace App\Domain\Department\Entities;

final class Department {
    public function __construct(
        private readonly ?int $id,
        private readonly string $deptNo,
        private readonly string $name,
        private readonly string $code,
        private readonly ?string $branch,
        private readonly bool $isOffice,
        private readonly bool $isActive,
    ) {}

    public function id(): ?int { return $this->id; }
    public function deptNo(): string { return $this->deptNo; }
    public function name(): string { return $this->name; }
    public function code(): string { return $this->code; }
    public function branch(): ?string { return $this->branch; }
    public function isOffice(): bool { return $this->isOffice; }
    public function isActive(): bool { return $this->isActive; }
}