<?php

namespace App\Domain\Verification\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;

interface VerificationReportRepository
{
    public function findById(int $id): ?VerificationReport;

    public function store(VerificationReport $report): VerificationReport;

    public function nextDocumentNumber(): string;
}
