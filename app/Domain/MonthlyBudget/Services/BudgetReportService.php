<?php

declare(strict_types=1);

namespace App\Domain\MonthlyBudget\Services;

use App\Imports\MonthlyBudgetReportImport;
use App\Models\MonthlyBudgetReport;
use App\Models\MonthlyBudgetReportDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

final class BudgetReportService
{
    public function __construct(private \App\Application\Approval\Contracts\Approvals $approvals) {}

    /**
     * Create budget report with items.
     */
    public function createReport(array $data, ?array $items = null): array
    {
        try {
            $report = MonthlyBudgetReport::create([
                'dept_no' => $data['dept_no'],
                'creator_id' => $data['creator_id'],
                'report_date' => $data['report_date'],
                'created_autograph' => $data['created_autograph'] ?? null,
                'is_known_autograph' => $data['is_known_autograph'] ?? null,
                'approved_autograph' => $data['approved_autograph'] ?? null,
            ]);

            if ($items) {
                foreach ($items as $item) {
                    MonthlyBudgetReportDetail::create([
                        'header_id' => $report->id,
                        'name' => $item['name'],
                        'spec' => $item['spec'] ?? null,
                        'uom' => $item['uom'],
                        'last_recorded_stock' => empty($item['last_recorded_stock']) ? null : $item['last_recorded_stock'],
                        'usage_per_month' => $item['usage_per_month'] ?? null,
                        'quantity' => empty($item['quantity']) ? 0 : $item['quantity'],
                        'remark' => $item['remark'],
                    ]);
                }
            }

            return [
                'success' => true,
                'message' => 'Monthly Budget Report created successfully',
                'report' => $report,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating report: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Create report from Excel import.
     */
    public function createFromExcel(array $data, $excelFile): array
    {
        try {
            DB::beginTransaction();

            $report = MonthlyBudgetReport::create([
                'dept_no' => $data['dept_no'],
                'creator_id' => $data['creator_id'],
                'report_date' => $data['report_date'],
                'created_autograph' => $data['created_autograph'] ?? null,
                'is_known_autograph' => $data['is_known_autograph'] ?? null,
                'approved_autograph' => $data['approved_autograph'] ?? null,
            ]);

            $import = new MonthlyBudgetReportImport(
                $data['dept_no'],
                $data['report_date'],
                $report->id
            );

            Excel::import($import, $excelFile);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Monthly Budget Report created successfully from Excel',
                'report' => $report,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing Excel file: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error importing Excel file',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Update budget report.
     */
    public function updateReport(int $reportId, array $data): array
    {
        $report = MonthlyBudgetReport::find($reportId);

        if (! $report) {
            return [
                'success' => false,
                'message' => 'Report not found',
            ];
        }

        $report->update($data);

        return [
            'success' => true,
            'message' => 'Monthly Budget Report successfully updated',
            'report' => $report,
        ];
    }

    /**
     * Delete budget report and its details.
     */
    public function deleteReport(int $reportId): array
    {
        $report = MonthlyBudgetReport::find($reportId);

        if (! $report) {
            return [
                'success' => false,
                'message' => 'Report not found',
            ];
        }

        if (!$report->isDraft()) {
            return [
                'success' => false,
                'message' => 'Only reports in Draft state can be deleted.',
            ];
        }

        MonthlyBudgetReportDetail::where('header_id', $reportId)->delete();
        $report->delete();

        return [
            'success' => true,
            'message' => 'Monthly Budget Report successfully deleted',
        ];
    }
    /**
     * Cancel budget report.
     */
    public function cancelReport(int $reportId, string $reason): array
    {
        $report = MonthlyBudgetReport::find($reportId);

        if (! $report) {
            return [
                'success' => false,
                'message' => 'Report not found',
            ];
        }

        // Use Approval Engine to cancel if it has started
        $this->approvals->cancel($report, auth()->id(), $reason);

        $report->update([
            'is_cancel' => true,
            'cancel_reason' => $reason,
            'status' => 5, // Canceled state in legacy
        ]);

        return [
            'success' => true,
            'message' => 'Monthly Budget Report successfully cancelled',
        ];
    }
}
