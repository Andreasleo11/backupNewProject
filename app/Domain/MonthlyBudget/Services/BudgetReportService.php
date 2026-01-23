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
                        'last_recorded_stock' => $item['last_recorded_stock'] ?? null,
                        'usage_per_month' => $item['usage_per_month'] ?? null,
                        'quantity' => $item['quantity'],
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

        MonthlyBudgetReportDetail::where('header_id', $reportId)->delete();
        $report->delete();

        return [
            'success' => true,
            'message' => 'Monthly Budget Report successfully deleted',
        ];
    }
}
