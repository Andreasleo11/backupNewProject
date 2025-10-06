<?php

namespace App\Livewire;

use App\Domain\Expenses\DTO\DepartmentTotal;
use App\Domain\Expenses\UseCases\GetDepartmentMonthlyTotals;
use App\Domain\Expenses\UseCases\GetDepartmentTotals;
use App\Domain\Expenses\UseCases\GetLatestAvailableMonth;
use App\Domain\Expenses\UseCases\GetRollingDepartmentTotals;
use App\Domain\Expenses\UseCases\ListPrSigners;
use Carbon\Carbon;
use Livewire\Component;

class DepartmentExpenses extends Component
{
    // app/Livewire/DepartmentExpenses.php

    public string $month;

    public ?int $deptId = null;

    public ?string $prSigner = null;

    public array $prSigners = [];

    public ?string $chartKeySent = null;

    public ?string $chartMonthSent = null; // track the last month we fed to the chart

    public string $compareMode = 'range'; // 'range' | 'rolling'

    public ?string $startMonth = null;    // for 'range'

    public ?string $endMonth = null;    // for 'range'

    public int $rollingN = 3;             // for 'rolling'

    public ?string $compareKeySent = null;     // event dedupe

    public string $activeTab = 'overview'; // --- Active tab (overview | detail | compare) ---

    public bool $skipChartClearOnce = false;

    public array $monthOptions = [];

    public function mount(GetLatestAvailableMonth $getLatest): void
    {
        $ym = $getLatest->execute(null); // Find latest month overall (no signer yet on first load)
        $this->month = $ym ?: now()->format('Y-m');
        $this->endMonth = $this->month;
        $this->startMonth = Carbon::parse($this->month.'-01')->subMonth()->format('Y-m');

        $start = \Illuminate\Support\Carbon::parse('2025-01-01');
        $end = \Illuminate\Support\Carbon::today()->startOfMonth();

        for ($d = $end->copy(); $d->gte($start); $d->subMonth()) {
            $this->monthOptions[] = [
                'value' => $d->format('Y-m'),
                'text' => $d->isoFormat('MMM YYYY'),
            ];
        }

        // dd($this->monthOptions);
    }

    public function updatedMonth(): void
    {
        $this->chartMonthSent = null;

        if (! $this->skipChartClearOnce) {
            $this->dispatch('chart:clearSelection');
        }

        $this->skipChartClearOnce = false;
    }

    public function updatedPrSigner(): void
    {
        $this->chartKeySent = null;
        $this->compareKeySent = null;
    }

    public function updatedStartMonth(): void
    {
        $this->compareKeySent = null;
    }

    public function updatedEndMonth(): void
    {
        $this->compareKeySent = null;
    }

    public function updatedRollingN(): void
    {
        $this->compareKeySent = null;
    }

    public function showDetail(int $deptId): void
    {
        $this->deptId = $deptId;
        $this->activeTab = 'detail';
    }

    public function clearDetail(): void
    {
        $this->deptId = null;
        $this->activeTab = 'overview';
        $this->dispatch('chart:clearSelection');
        $this->dispatch('compare:clearSelection');
    }

    public function render(
        GetDepartmentTotals $getTotals,
        ListPrSigners $listSigners,
        GetDepartmentMonthlyTotals $getMonthlyTotals,
        GetRollingDepartmentTotals $getRollingTotals
    ) {
        $signers = $listSigners->execute($this->month);
        $this->prSigners = $signers;

        if ($this->prSigner !== null && ! in_array($this->prSigner, $signers, true)) {
            $this->prSigner = null;
            $this->chartKeySent = null;
            $this->compareKeySent = null;
        }

        $totalsDto = $getTotals->execute($this->month, $this->prSigner);
        $totals = collect($totalsDto)
            ->map(fn (DepartmentTotal $d) => (object) [
                'dept_id' => $d->deptId,
                'dept_name' => $d->deptName,
                'dept_no' => $d->deptNo,
                'total_expense' => $d->totalExpense,
            ])
            ->sortByDesc('total_expense')
            ->values();

        if ($this->deptId !== null) {
            $validDeptIds = $totals->pluck('dept_id')->all();
            if (! in_array($this->deptId, $validDeptIds, true)) {
                $this->deptId = null;
                $this->dispatch('chart:clearSelection');
            }
        }

        // chart data seeded when (month|signer) changes
        $key = $this->month.'|'.($this->prSigner ?? '');
        if ($this->chartKeySent !== $key) {
            $this->dispatch('chart:render', data: [
                'labels' => $totals->pluck('dept_name')->values(),
                'data' => $totals->pluck('total_expense')->map(fn ($v) => (float) $v)->values(),
                'deptIds' => $totals->pluck('dept_id')->map(fn ($v) => (int) $v)->values(),
            ]);
            $this->chartKeySent = $key;
        }

        // Normalize inputs
        if ($this->compareMode === 'range') {
            $startYm = $this->startMonth ?: $this->month;
            $endYm = $this->endMonth ?: $this->month;

            // ensure start <= end
            $s = Carbon::parse($startYm.'-01');
            $e = Carbon::parse($endYm.'-01');
            if ($s->gt($e)) {
                // not returning any data
            }

            $cmp = $getMonthlyTotals->execute($startYm, $endYm, $this->prSigner);
            $cmpKey = "range|$startYm|$endYm|".($this->prSigner ?? 'all');
        } else {
            $n = max(2, min(6, (int) $this->rollingN));
            $endYm = $this->endMonth ?: $this->month;
            $cmp = $getRollingTotals->execute($endYm, $n, $this->prSigner);
            $cmpKey = "rolling|$endYm|$n|".($this->prSigner ?? 'all');
        }

        // Build Chart.js payload: labels are departments, datasets per month
        $labels = array_map(fn ($d) => $d['deptName'], $cmp['departments']);
        $deptIds = array_map(fn ($d) => $d['deptId'], $cmp['departments']);
        $months = $cmp['months'];

        // Transpose departments x months → datasets (one per month)
        $datasets = [];
        foreach ($months as $mi => $ym) {
            $datasets[] = [
                'label' => Carbon::parse($ym.'-01')->isoFormat('MMM YYYY'),
                'data' => array_map(fn ($d) => (float) ($d['series'][$mi] ?? 0), $cmp['departments']),
            ];
        }

        // Optional: build Δ/Δ% rows if range with exactly 2 months
        $deltas = [];
        if ($this->compareMode === 'range' && count($months) === 2) {
            [$m0, $m1] = $months;
            foreach ($cmp['departments'] as $d) {
                $a = (float) $d['series'][0];
                $b = (float) $d['series'][1];
                $diff = $b - $a;
                $pct = $a == 0.0 ? null : ($diff / $a) * 100.0;
                $deltas[] = (object) [
                    'dept_id' => $d['deptId'],
                    'dept_name' => $d['deptName'],
                    'a' => $a,
                    'b' => $b,
                    'diff' => $diff,
                    'pct' => $pct,
                ];
            }
            // sort by largest absolute change desc
            usort($deltas, fn ($x, $y) => abs($y->diff) <=> abs($x->diff));
        }

        $this->dispatch('compare:render', data: [
            'labels' => $labels,
            'deptIds' => $deptIds,
            'months' => $months,
            'datasets' => $datasets,
        ]);

        $this->compareKeySent = $cmpKey;

        return view('livewire.department-expenses', compact('totals'));
    }
}
