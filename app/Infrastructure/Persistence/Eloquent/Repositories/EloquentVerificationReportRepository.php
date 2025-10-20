<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Verification\Repositories\VerificationReportRepository;
use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;

final class EloquentVerificationReportRepository implements VerificationReportRepository
{
    public function findById(int $id): ?VerificationReport
    {
        return VerificationReport::with(['items', 'items.defects'])->find($id);
    }

    public function store(VerificationReport $report): VerificationReport
    {
        $report->save();

        return $report->fresh(['items', 'items.defects']);
    }

    public function nextDocumentNumber(): string
    {
        $prefix = 'VQC-'.now()->format('Ymd').'-';
        $last = VerificationReport::where('document_number', 'like', $prefix.'%')
            ->orderByDesc('id')->value('document_number');
        $n = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix.str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }
}
