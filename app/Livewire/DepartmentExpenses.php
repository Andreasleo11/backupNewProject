<?php

namespace App\Livewire;

use App\Domain\Expenses\ExpenseRepository;
use Livewire\Component;

class DepartmentExpenses extends Component
{
    // app/Livewire/DepartmentExpenses.php

    public string $month;

    public ?int $deptId = null;

    public ?string $prSigner = null;        // 👈 selected approver

    public array $prSigners = [];           // 👈 dropdown options

    public ?string $chartKeySent = null;    // month|signer key

    // Track the last month we fed to the chart
    public ?string $chartMonthSent = null;

    public function mount(): void
    {
        $this->month = now()->format('Y-m');
    }

    public function updatedMonth(): void
    {
        $this->chartMonthSent = null;
        $this->dispatch('chart:clearSelection');
    }

    public function updatedPrSigner(): void
    {
        $this->chartKeySent = null;         // chart depends on signer too
    }

    public function render(ExpenseRepository $repo)
    {
        // dropdown values for this month
        $this->prSigners = $repo->prSignersForMonth($this->month)->values()->all();

        // totals respect PR signer filter
        $totals = $repo->totalsPerDepartmentForMonth($this->month, $this->prSigner);

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

        return view('livewire.department-expenses', compact('totals'));
    }

    public function showDetail(int $deptId): void
    {
        $this->deptId = $deptId;
    }

    public function clearDetail(): void
    {
        $this->deptId = null;
        $this->dispatch('chart:clearSelection');
    }
}
