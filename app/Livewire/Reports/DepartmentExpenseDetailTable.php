<?php

namespace App\Livewire\Reports;

use App\Domain\Expenses\DTO\ExpenseLine;
use App\Domain\Expenses\UseCases\GetExpenseDetail;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentExpenseDetailTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    protected string $pageName = 'detailPage';

    public string $month;

    public int $deptId;

    public string $deptName = '';

    public string $monthLabel = '';

    public ?string $prSigner = null;   // 👈 add

    public string $search = '';

    public int $perPage = 25;

    public string $sortBy = 'expense_date';

    public string $sortDir = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 25],
        'sortBy' => ['except' => 'expense_date'],
        'sortDir' => ['except' => 'desc'],
    ];

    public function mount(
        int $deptId,
        string $month,
        string $deptName = '',
        string $monthLabel = '',
        ?string $prSigner = null,
    ): void {
        $this->deptId = $deptId;
        $this->month = $month;
        $this->deptName = $deptName;
        $this->monthLabel = $monthLabel;
        $this->prSigner = $prSigner;
    }

    protected function getPageName(): string
    {
        return $this->pageName;
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
            'expense_date',
            'source',
            'item_name',
            'quantity',
            'uom',
            'unit_price',
            'line_total',
        ];
        if (! in_array($field, $whitelist, true)) {
            return;
        }

        if ($this->sortBy === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDir = 'asc';
        }
        $this->resetPage();
    }

    public function render(GetExpenseDetail $getDetail)
    {
        $page = Paginator::resolveCurrentPage($this->getPageName());

        $resp = $getDetail->execute(
            deptId: $this->deptId,
            ym: $this->month,
            prSigner: $this->prSigner,
            sortBy: $this->sortBy,
            sortDir: $this->sortDir,
            page: $page,
            perPage: $this->perPage,
            search: $this->search,
        );

        $items = collect($resp['items'])->map(function (ExpenseLine $l) {
            return (object) [
                'expense_date' => $l->expenseDate->format('Y-m-d'),
                'source' => $l->source,
                'autograph_5' => $l->autograph5,
                'doc_id' => $l->docId,
                'doc_num' => $l->docNum,
                'item_name' => $l->itemName,
                'uom' => $l->uom,
                'quantity' => $l->quantity,
                'unit_price' => $l->unitPrice,
                'line_total' => $l->lineTotal,
            ];
        });

        $sumTotal = $resp['sumTotal'] ?? (float) $items->sum('line_total');

        $rows = new LengthAwarePaginator(
            items: $items,
            total: (int) $resp['total'],
            perPage: (int) $resp['perPage'],
            currentPage: (int) $resp['page'],
            options: [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view(
            'livewire.reports.department-expense-detail-table',
            compact('rows', 'sumTotal'),
        );
    }
}
