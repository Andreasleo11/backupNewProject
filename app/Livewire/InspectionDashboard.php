<?php

namespace App\Livewire;

use App\Models\InspectionForm\InspectionDimension;
use App\Models\InspectionForm\InspectionProblem;
use App\Models\InspectionForm\InspectionReport;
use App\Models\InspectionQuantity;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;

class InspectionDashboard extends Component
{
    // ── URL-bound filters ───────────────────────────────────────────────
    #[Url(as: 'from', except: '')]
    public string $dateFrom = '';

    #[Url(as: 'to', except: '')]
    public string $dateTo = '';

    #[Url(as: 'customer', except: '')]
    public string $customer = '';

    #[Url(as: 'part', except: '')]
    public string $partNumber = '';

    // ── Computed data (populated in computeData) ────────────────────────
    public array $kpi = [];

    public array $trendChart = [];   // inspections per day

    public array $shiftChart = [];   // reports by shift

    public array $customerChart = [];  // top 10 customers

    public array $passRejectChart = []; // pass/reject per day

    public array $topFailingParts = [];

    public array $topProblemTypes = [];

    public array $latestReports = [];

    public array $dimensionFailures = [];

    // ── Option lists for filter dropdowns ──────────────────────────────
    public array $customerOptions = [];

    // ── Internal flag ───────────────────────────────────────────────────
    public bool $ready = false;

    public function mount(): void
    {
        // Only apply defaults when no URL params have pre-set the filters
        if ($this->dateFrom === '') {
            $this->dateFrom = Carbon::now()->startOfMonth()->toDateString();
        }
        if ($this->dateTo === '') {
            $this->dateTo = Carbon::now()->toDateString();
        }

        $this->customerOptions = InspectionReport::query()
            ->select('customer')
            ->distinct()
            ->orderBy('customer')
            ->pluck('customer')
            ->toArray();

        // ── Smart fallback ───────────────────────────────────────────────
        // If the default date range has no data (e.g. early in the month,
        // or data was entered for previous months only), automatically
        // shift to the last 30 days of actual data so the dashboard always
        // opens with something visible rather than a sea of zeros.
        $hasData = InspectionReport::query()
            ->whereDate('inspection_date', '>=', $this->dateFrom)
            ->whereDate('inspection_date', '<=', $this->dateTo)
            ->exists();

        if (! $hasData) {
            $latestDate = InspectionReport::query()
                ->max('inspection_date');

            if ($latestDate) {
                $end = Carbon::parse($latestDate);
                $this->dateTo = $end->toDateString();
                $this->dateFrom = $end->copy()->subDays(29)->toDateString();
            }
            // If there are no records at all, keep the current-month range
            // and let the charts render empty — at least the UI is shown.
        }

        $this->computeData();
        $this->ready = true;
    }

    public function updatedDateFrom(): void
    {
        $this->computeData();
    }

    public function updatedDateTo(): void
    {
        $this->computeData();
    }

    public function updatedCustomer(): void
    {
        $this->computeData();
    }

    public function updatedPartNumber(): void
    {
        $this->computeData();
    }

    public function clearFilters(): void
    {
        $this->dateFrom = Carbon::now()->startOfMonth()->toDateString();
        $this->dateTo = Carbon::now()->toDateString();
        $this->customer = '';
        $this->partNumber = '';
        $this->computeData();
    }

    // ── Base query scope ─────────────────────────────────────────────────
    private function baseReportQuery()
    {
        return InspectionReport::query()
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('inspection_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('inspection_date', '<=', $this->dateTo))
            ->when($this->customer !== '', fn ($q) => $q->where('customer', $this->customer))
            ->when($this->partNumber !== '', fn ($q) => $q->where('part_number', 'like', '%' . $this->partNumber . '%'));
    }

    private function baseQuantityQuery()
    {
        return InspectionQuantity::query()
            ->join(
                'inspection_reports',
                'inspection_quantities.inspection_report_document_number',
                '=',
                'inspection_reports.document_number',
            )
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('inspection_reports.inspection_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('inspection_reports.inspection_date', '<=', $this->dateTo))
            ->when($this->customer !== '', fn ($q) => $q->where('inspection_reports.customer', $this->customer))
            ->when($this->partNumber !== '', fn ($q) => $q->where('inspection_reports.part_number', 'like', '%' . $this->partNumber . '%'));
    }

    // ── Main data computation ─────────────────────────────────────────────
    private function computeData(): void
    {
        $this->computeKpi();
        $this->computeTrendChart();
        $this->computeShiftChart();
        $this->computeCustomerChart();
        $this->computePassRejectChart();
        $this->computeTopFailingParts();
        $this->computeTopProblemTypes();
        $this->computeLatestReports();
        $this->computeDimensionFailures();

        // Broadcast fresh data to JS so Chart.js can update without a page reload.
        // wire:ignore keeps the <canvas> elements alive; this event re-draws them.
        $this->dispatch(
            'charts-ready',
            trend: $this->trendChart,
            shift: $this->shiftChart,
            customer: $this->customerChart,
            passReject: $this->passRejectChart,
        );
    }

    private function computeKpi(): void
    {
        $totalReports = $this->baseReportQuery()->count();

        $qtyStats = $this->baseQuantityQuery()
            ->selectRaw('
                COALESCE(SUM(output_quantity), 0)   AS total_output,
                COALESCE(SUM(pass_quantity), 0)     AS total_pass,
                COALESCE(SUM(reject_quantity), 0)   AS total_reject,
                COALESCE(AVG(pass_rate), 0)         AS avg_pass_rate,
                COALESCE(AVG(reject_rate), 0)       AS avg_reject_rate,
                COALESCE(AVG(ng_sample_rate), 0)    AS avg_ng_sample_rate
            ')
            ->first();

        $this->kpi = [
            'total_reports' => $totalReports,
            'total_output' => (int) ($qtyStats->total_output ?? 0),
            'total_pass' => (int) ($qtyStats->total_pass ?? 0),
            'total_reject' => (int) ($qtyStats->total_reject ?? 0),
            'avg_pass_rate' => round((float) ($qtyStats->avg_pass_rate ?? 0), 1),
            'avg_reject_rate' => round((float) ($qtyStats->avg_reject_rate ?? 0), 1),
            'avg_ng_sample_rate' => round((float) ($qtyStats->avg_ng_sample_rate ?? 0), 1),
        ];
    }

    private function computeTrendChart(): void
    {
        $rows = $this->baseReportQuery()
            ->selectRaw('DATE(inspection_date) AS day, COUNT(*) AS cnt')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $this->trendChart = [
            'labels' => $rows->pluck('day')->toArray(),
            'data' => $rows->pluck('cnt')->map(fn ($v) => (int) $v)->toArray(),
        ];
    }

    private function computeShiftChart(): void
    {
        $rows = $this->baseReportQuery()
            ->selectRaw('shift, COUNT(*) AS cnt')
            ->groupBy('shift')
            ->orderBy('shift')
            ->get();

        $this->shiftChart = [
            'labels' => $rows->pluck('shift')->map(fn ($s) => "Shift {$s}")->toArray(),
            'data' => $rows->pluck('cnt')->map(fn ($v) => (int) $v)->toArray(),
        ];
    }

    private function computeCustomerChart(): void
    {
        $rows = $this->baseReportQuery()
            ->selectRaw('customer, COUNT(*) AS cnt')
            ->groupBy('customer')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();

        $this->customerChart = [
            'labels' => $rows->pluck('customer')->toArray(),
            'data' => $rows->pluck('cnt')->map(fn ($v) => (int) $v)->toArray(),
        ];
    }

    private function computePassRejectChart(): void
    {
        $rows = $this->baseQuantityQuery()
            ->selectRaw('DATE(inspection_reports.inspection_date) AS day, SUM(pass_quantity) AS pass_qty, SUM(reject_quantity) AS reject_qty')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $this->passRejectChart = [
            'labels' => $rows->pluck('day')->toArray(),
            'pass' => $rows->pluck('pass_qty')->map(fn ($v) => (int) $v)->toArray(),
            'reject' => $rows->pluck('reject_qty')->map(fn ($v) => (int) $v)->toArray(),
        ];
    }

    private function computeTopFailingParts(): void
    {
        $this->topFailingParts = $this->baseQuantityQuery()
            ->selectRaw('inspection_reports.part_number, inspection_reports.part_name, COUNT(*) AS reports, AVG(reject_rate) AS avg_reject, SUM(reject_quantity) AS total_reject')
            ->groupBy('inspection_reports.part_number', 'inspection_reports.part_name')
            ->orderByDesc('avg_reject')
            ->limit(10)
            ->get()
            ->map(fn ($r) => [
                'part_number' => $r->part_number,
                'part_name' => $r->part_name,
                'reports' => (int) $r->reports,
                'avg_reject' => round((float) $r->avg_reject, 2),
                'total_reject' => (int) $r->total_reject,
            ])
            ->toArray();
    }

    private function computeTopProblemTypes(): void
    {
        $rows = InspectionProblem::query()
            ->join(
                'inspection_reports',
                'inspection_problems.inspection_report_document_number',
                '=',
                'inspection_reports.document_number',
            )
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('inspection_reports.inspection_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('inspection_reports.inspection_date', '<=', $this->dateTo))
            ->when($this->customer !== '', fn ($q) => $q->where('inspection_reports.customer', $this->customer))
            ->when($this->partNumber !== '', fn ($q) => $q->where('inspection_reports.part_number', 'like', '%' . $this->partNumber . '%'))
            ->selectRaw('inspection_problems.type, COUNT(*) AS cnt')
            ->groupBy('inspection_problems.type')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get();

        $this->topProblemTypes = $rows->map(fn ($r) => [
            'type' => $r->type ?: 'Unknown',
            'count' => (int) $r->cnt,
        ])->toArray();
    }

    private function computeLatestReports(): void
    {
        $this->latestReports = $this->baseReportQuery()
            ->select(['id', 'document_number', 'customer', 'part_number', 'inspection_date', 'shift'])
            ->orderByDesc('inspection_date')
            ->orderByDesc('id')
            ->limit(8)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'document_number' => $r->document_number,
                'customer' => $r->customer,
                'part_number' => $r->part_number,
                'inspection_date' => Carbon::parse($r->inspection_date)->format('d M Y'),
                'shift' => $r->shift,
            ])
            ->toArray();
    }

    private function computeDimensionFailures(): void
    {
        // Get the table name dynamically to be future-proof
        $model = new InspectionDimension;
        $table = $model->getTable();

        $rows = DB::table($table)
            ->join(
                'inspection_reports',
                "{$table}.inspection_report_document_number",
                '=',
                'inspection_reports.document_number',
            )
            ->where("{$table}.judgement", 'NG')
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('inspection_reports.inspection_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('inspection_reports.inspection_date', '<=', $this->dateTo))
            ->when($this->customer !== '', fn ($q) => $q->where('inspection_reports.customer', $this->customer))
            ->when($this->partNumber !== '', fn ($q) => $q->where('inspection_reports.part_number', 'like', '%' . $this->partNumber . '%'))
            ->selectRaw("{$table}.area, COUNT(*) AS ng_count")
            ->groupBy("{$table}.area")
            ->orderByDesc('ng_count')
            ->limit(10)
            ->get();

        $this->dimensionFailures = $rows->map(fn ($r) => [
            'area' => $r->area ?: 'Unknown',
            'ng_count' => (int) $r->ng_count,
        ])->toArray();
    }

    public function render()
    {
        return view('livewire.inspection-form.dashboard')
            ->layout('layouts.guest');
    }
}
