<?php

namespace App\Livewire\PurchaseRequest;

use App\Application\PurchaseRequest\Queries\Filters\DateRangeFilter;
use App\Application\PurchaseRequest\Queries\Filters\DepartmentFilter;
use App\Application\PurchaseRequest\Queries\Filters\GlobalSearchFilter;
use App\Application\PurchaseRequest\Queries\Filters\StatusFilter;
use App\Application\PurchaseRequest\Queries\PurchaseRequestQueryBuilder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('new.layouts.app')]
class PurchaseRequestApprovedAll extends Component
{
    use WithPagination;

    // View state
    public $activeDrawer = null;

    // Filters
    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $department = '';

    #[Url(as: 'date_range')]
    public string $dateRange = '';

    #[Url]
    public string $branch = '';

    #[Url]
    public string $preset = 'all';

    // Sorting
    #[Url(as: 'sort')]
    public string $sortField = 'id';

    #[Url(as: 'dir')]
    public string $sortDirection = 'desc';

    // Pagination
    #[Url(as: 'per_page')]
    public int $perPage = 10;

    #[Url]
    public int $page = 1;

    // Allowed sort columns — centralised so sortBy() and queryRows() stay in sync
    private const ALLOWED_SORTS = ['id', 'pr_no', 'doc_num', 'date_pr', 'created_at', 'supplier', 'from_department', 'po_number'];

    public function mount(): void
    {
        $this->resetPage();
    }

    public function updated(string $name): void
    {
        $filterProps = ['search', 'department', 'dateRange', 'branch', 'perPage', 'sortField', 'sortDirection'];

        if (in_array($name, $filterProps, true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->department = '';
        $this->dateRange = '';
        $this->branch = '';
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->resetFilters();
    }

    public function clearFilter(string $filter): void
    {
        match ($filter) {
            'search' => $this->search = '',
            'department' => $this->department = '',
            'dateRange' => $this->dateRange = '',
            'branch' => $this->branch = '',
            default => null,
        };
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, self::ALLOWED_SORTS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    protected function queryRows()
    {
        $builder = new PurchaseRequestQueryBuilder;
        // Bypass scoping for global approved view
        $query = \App\Models\PurchaseRequest::query()
            ->withCount('items')
            ->with([
                'createdBy:id,name',
                'approvalRequest:id,approvable_id,approvable_type,status,current_step',
            ]);

        $filters = [];

        // Force status to APPROVED
        $filters[] = new StatusFilter('APPROVED');

        if ($this->search) {
            $filters[] = new GlobalSearchFilter($this->search);
        }
        if ($this->department) {
            $filters[] = new DepartmentFilter($this->department);
        }
        if ($this->dateRange) {
            $filters[] = DateRangeFilter::fromString($this->dateRange);
        }
        if ($this->branch) {
            $filters[] = new \App\Application\PurchaseRequest\Queries\Filters\BranchFilter($this->branch);
        }

        $sortColumn = in_array($this->sortField, self::ALLOWED_SORTS, true) ? $this->sortField : 'id';
        $sortDir = in_array(strtolower($this->sortDirection), ['asc', 'desc'], true) ? $this->sortDirection : 'desc';

        return $builder->withFilters($query, $filters)->orderBy($sortColumn, $sortDir);
    }

    #[Computed]
    public function departments(): array
    {
        return \App\Enums\ToDepartment::values();
    }

    #[Computed]
    public function statuses(): array
    {
        return ['APPROVED'];
    }

    #[Computed]
    public function branches(): array
    {
        return \App\Enums\Branch::values();
    }

    public function render()
    {
        $rows = $this->queryRows()->paginate($this->perPage);

        return view('livewire.purchase-request.purchase-request-approved-all', [
            'rows' => $rows,
            'departments' => $this->departments,
            'statuses' => $this->statuses,
            'branches' => $this->branches,
        ]);
    }
}
