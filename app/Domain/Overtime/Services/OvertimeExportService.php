<?php

declare(strict_types=1);

namespace App\Domain\Overtime\Services;

use App\Domain\Overtime\Models\OvertimeForm;
use App\Domain\Overtime\Models\OvertimeFormDetail;
use App\Exports\OvertimeExport;
use App\Exports\OvertimeExportExample;
use App\Exports\OvertimeSummaryExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class OvertimeExportService
{
    /**
     * Export overtime data to Excel for a specific header.
     */
    public function exportOvertime(int $headerId): BinaryFileResponse
    {
        $header = OvertimeForm::with('department')->findOrFail($headerId);
        $datas = OvertimeFormDetail::where('header_id', $headerId)->get();

        $departmentName = $header->department->name;
        $currentDate = Carbon::now()->format('d-m-y');

        $fileName = "overtime_{$departmentName}_{$currentDate}.xlsx";

        // Mark as exported
        $header->update(['is_export' => true]);

        return Excel::download(new OvertimeExport($header, $datas), $fileName);
    }

    /**
     * Download overtime Excel template for import.
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new OvertimeExportExample, 'overtime_template.xlsx');
    }

    /**
     * Export overtime summary for a date range.
     */
    public function exportSummary(string $startDate, string $endDate): BinaryFileResponse
    {
        return Excel::download(
            new OvertimeSummaryExport($startDate, $endDate),
            'Overtime-Summary.xlsx'
        );
    }
}
