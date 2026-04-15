<?php

declare(strict_types=1);

namespace App\Domain\MonthlyBudget\Services;

use App\Models\MonthlyBudgetReport;
use App\Models\MonthlyBudgetReportSummaryDetail;
use App\Models\MonthlyBudgetSummaryReport;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class BudgetSummaryService
{
    /**
     * Create summary report from monthly budget reports.
     */
    public function createSummary(array $data, array $departmentReports): array
    {
        try {
            $summary = MonthlyBudgetSummaryReport::create([
                'dept_no' => $data['dept_no'],
                'creator_id' => $data['creator_id'],
                'report_date' => $data['report_date'],
                'is_moulding' => $data['is_moulding'] ?? false,
            ]);

            // Process each department's budget data
            foreach ($departmentReports as $deptNo => $reportIds) {
                $aggregatedData = $this->aggregateDepartmentBudgets($reportIds);

                foreach ($aggregatedData as $item) {
                    MonthlyBudgetReportSummaryDetail::create([
                        'header_id' => $summary->id,
                        'dept_no' => $deptNo,
                        'name' => $item['name'],
                        'spec' => $item['spec'] ?? null,
                        'uom' => $item['uom'],
                        'last_recorded_stock' => $item['last_recorded_stock'] ?? 0,
                        'usage_per_month' => $item['usage_per_month'] ?? null,
                        'quantity' => $item['quantity'],
                        'remark' => $item['remark'] ?? null,
                    ]);
                }
            }

            return [
                'success' => true,
                'message' => 'Summary report created successfully',
                'summary' => $summary,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating summary: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Aggregate budget data from multiple reports.
     */
    private function aggregateDepartmentBudgets(array $reportIds): Collection
    {
        $reports = MonthlyBudgetReport::with('details')
            ->whereIn('id', $reportIds)
            ->get();

        $aggregated = [];

        foreach ($reports as $report) {
            foreach ($report->details as $detail) {
                $key = $detail->name . '|' . ($detail->spec ?? '');

                if (! isset($aggregated[$key])) {
                    $aggregated[$key] = [
                        'name' => $detail->name,
                        'spec' => $detail->spec,
                        'uom' => $detail->uom,
                        'last_recorded_stock' => $detail->last_recorded_stock ?? 0,
                        'usage_per_month' => $detail->usage_per_month,
                        'quantity' => $detail->quantity,
                        'remark' => $detail->remark,
                    ];
                } else {
                    // Aggregate quantities
                    $aggregated[$key]['quantity'] += $detail->quantity;
                    $aggregated[$key]['last_recorded_stock'] += ($detail->last_recorded_stock ?? 0);
                }
            }
        }

        return collect(array_values($aggregated));
    }

    /**
     * Refresh summary by recalculating from source reports.
     */
    public function refreshSummary(int $summaryId): array
    {
        $summary = MonthlyBudgetSummaryReport::with('details')->find($summaryId);

        if (! $summary) {
            return [
                'success' => false,
                'message' => 'Summary not found',
            ];
        }

        // Delete existing details
        MonthlyBudgetReportSummaryDetail::where('header_id', $summaryId)->delete();

        // Get source reports for the same period and departments
        $reportDate = Carbon::parse($summary->report_date);
        $month = $reportDate->month;
        $year = $reportDate->year;

        $reports = MonthlyBudgetReport::with('details')
            ->whereYear('report_date', $year)
            ->whereMonth('report_date', $month)
            ->whereHas('approvalRequest', fn ($q) => $q->where('status', 'APPROVED')) // Only approved reports
            ->when($summary->is_moulding, function ($q) {
                return $q->where('dept_no', '363');
            })
            ->when(! $summary->is_moulding, function ($q) {
                return $q->where('dept_no', '!=', '363');
            })
            ->get()
            ->groupBy('dept_no');

        foreach ($reports as $deptNo => $deptReports) {
            $aggregatedData = $this->aggregateDepartmentBudgets($deptReports->pluck('id')->toArray());

            foreach ($aggregatedData as $item) {
                MonthlyBudgetReportSummaryDetail::create([
                    'header_id' => $summaryId,
                    'dept_no' => $deptNo,
                    'name' => $item['name'],
                    'spec' => $item['spec'] ?? null,
                    'uom' => $item['uom'],
                    'last_recorded_stock' => $item['last_recorded_stock'] ?? 0,
                    'usage_per_month' => $item['usage_per_month'] ?? null,
                    'quantity' => $item['quantity'],
                    'remark' => $item['remark'] ?? null,
                ]);
            }
        }

        return [
            'success' => true,
            'message' => 'Summary refreshed successfully',
        ];
    }

    /**
     * Clone an existing summary for a new month.
     */
    public function cloneSummary(int $id, string $targetMonth): array
    {
        try {
            $source = MonthlyBudgetSummaryReport::with('details')->find($id);

            if (! $source) {
                return [
                    'success' => false,
                    'message' => 'Source summary not found',
                ];
            }

            $newSummary = MonthlyBudgetSummaryReport::create([
                'report_date' => Carbon::parse($targetMonth)->startOfMonth(),
                'dept_no' => $source->dept_no,
                'creator_id' => auth()->id(),
                'is_moulding' => $source->is_moulding,
            ]);

            foreach ($source->details as $detail) {
                MonthlyBudgetReportSummaryDetail::create([
                    'header_id' => $newSummary->id,
                    'dept_no' => $detail->dept_no,
                    'name' => $detail->name,
                    'spec' => $detail->spec,
                    'uom' => $detail->uom,
                    'last_recorded_stock' => $detail->last_recorded_stock,
                    'usage_per_month' => $detail->usage_per_month,
                    'quantity' => $detail->quantity,
                    'supplier' => $detail->supplier,
                    'cost_per_unit' => $detail->cost_per_unit,
                    'remark' => 'Cloned from ' . $source->doc_num,
                ]);
            }

            return [
                'success' => true,
                'message' => 'Summary cloned successfully',
                'summary' => $newSummary,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error cloning summary: ' . $e->getMessage(),
            ];
        }
    }
}
