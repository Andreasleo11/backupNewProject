<?php

namespace App\Domain\Employee\Entities;

class Employee
{
    public function __construct(
        private int $id,
        private string $nik,
        private string $name,
        private ?string $branch = null,
        private ?string $deptCode = null,
        private ?\DateTimeImmutable $startDate = null,
        private ?\DateTimeImmutable $endDate = null,
    ) {}

    public function id(): int {return $this->id; }

    public function nik(): string { return $this->nik; }

    public function name(): string { return $this->name; }

    public function branch(): ?string { return $this->branch; }

    public function deptCode(): ?string { return $this->deptCode; }

    public function startDate(): ?\DateTimeImmutable { return $this->startDate; }

    public function endDate(): ?\DateTimeImmutable { return $this->endDate;}
}
