<?php

namespace App\Livewire\Reports;

use App\Domain\Expenses\ExpenseRepository;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentExpenseDetailTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = "bootstrap";

    public string $month;
    public int $deptId;
    public string $deptName = "";
    public string $monthLabel = "";

    public string $search = "";
    public int $perPage = 25;
    public string $sortBy = "expense_date";
    public string $sortDir = "desc";

    protected $queryString = [
        "search" => ["except" => ""],
        "perPage" => ["except" => 25],
        "sortBy" => ["except" => "expense_date"],
        "sortDir" => ["except" => "desc"],
    ];

    public function mount(
        int $deptId,
        string $month,
        string $deptName = "",
        string $monthLabel = "",
    ): void {
        $this->deptId = $deptId;
        $this->month = $month;
        $this->deptName = $deptName;
        $this->monthLabel = $monthLabel;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
    }
    public function updatingMonth()
    {
        $this->resetPage();
    }
    public function updatingDeptId()
    {
        $this->resetPage();
    }

    public function sort(string $field): void
    {
        $whitelist = [
            "expense_date",
            "source",
            "item_name",
            "quantity",
            "uom",
            "unit_price",
            "line_total",
        ];
        if (!in_array($field, $whitelist, true)) {
            return;
        }

        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === "asc" ? "desc" : "asc";
        } else {
            $this->sortBy = $field;
            $this->sortDir = "asc";
        }
        $this->resetPage();
    }

    public function render(ExpenseRepository $repo)
    {
        $q = $repo->detailQueryForMonth($this->deptId, $this->month);

        if ($this->search !== "") {
            $term = "%" . $this->search . "%";
            $q->where(function ($qq) use ($term) {
                $qq->where("item_name", "like", $term)
                    ->orWhere("source", "like", $term)
                    ->orWhere("uom", "like", $term);
            });
        }

        // totals for the filtered set (not just current page)
        $sumQty = (clone $q)->sum("quantity");
        $sumTotal = (clone $q)->sum("line_total");

        $q->reorder();

        // apply sort + pagination (safe due to whitelist)
        $rows = $q->orderBy($this->sortBy, $this->sortDir)->paginate($this->perPage);

        return view(
            "livewire.reports.department-expense-detail-table",
            compact("rows", "sumQty", "sumTotal"),
        );
    }
}
