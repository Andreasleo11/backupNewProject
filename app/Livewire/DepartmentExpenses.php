<?php

namespace App\Livewire;

use App\Domain\Expenses\ExpenseRepository;
use Livewire\Component;

class DepartmentExpenses extends Component
{
    // app/Livewire/DepartmentExpenses.php

    public string $month;
    public ?int $deptId = null;

    // Track the last month we fed to the chart
    public ?string $chartMonthSent = null;

    public function mount(): void
    {
        $this->month = now()->format("Y-m");
    }

    public function updatedMonth(): void
    {
        $this->chartMonthSent = null;
        $this->dispatch("chart:clearSelection");
    }

    public function render(\App\Domain\Expenses\ExpenseRepository $repo)
    {
        $totals = $repo->totalsPerDepartmentForMonth($this->month);
        $detail = $this->deptId
            ? $repo->detailByDepartmentForMonth($this->deptId, $this->month)
            : collect();

        // ✅ Seed/refresh the chart ONLY when the month changes (or first load)
        if ($this->chartMonthSent !== $this->month) {
            $this->dispatch(
                "chart:render",
                data: [
                    "labels" => $totals->pluck("dept_name")->values(),
                    "data" => $totals->pluck("total_expense")->map(fn($v) => (float) $v)->values(),
                    "deptIds" => $totals->pluck("dept_id")->map(fn($v) => (int) $v)->values(),
                ],
            );
            $this->chartMonthSent = $this->month;
        }

        return view("livewire.department-expenses", compact("totals", "detail"));
    }

    public function showDetail(int $deptId): void
    {
        $this->deptId = $deptId;
    }

    public function clearDetail(): void
    {
        $this->deptId = null;
        $this->dispatch("chart:clearSelection");
    }
}
