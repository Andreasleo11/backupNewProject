<?php

namespace App\Livewire;

use App\Domain\Expenses\DTO\DepartmentTotal;
use App\Domain\Expenses\UseCases\GetDepartmentTotals;
use App\Domain\Expenses\UseCases\ListPrSigners;
use Livewire\Component;

class DepartmentExpenses extends Component
{
    // app/Livewire/DepartmentExpenses.php

    public string $month;

    public ?int $deptId = null;

    public ?string $prSigner = null;

    public array $prSigners = [];

    public ?string $chartKeySent = null;

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
        $this->chartKeySent = null;
    }

    public function render(GetDepartmentTotals $getTotals, ListPrSigners $listSigners)
    {
        $signers = $listSigners->execute($this->month);
        $this->prSigners = $signers;

        if ($this->prSigner !== null && ! in_array($this->prSigner, $signers, true)) {
            $this->prSigner = null;
            $this->chartKeySent = null;
        }

        $totalsDto = $getTotals->execute($this->month, $this->prSigner);
        $totals = collect($totalsDto)->map(function (DepartmentTotal $d) {
            return (object) [
                'dept_id' => $d->deptId,
                'dept_name' => $d->deptName,
                'dept_no' => $d->deptNo,
                'total_expense' => $d->totalExpense,
            ];
        });

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
