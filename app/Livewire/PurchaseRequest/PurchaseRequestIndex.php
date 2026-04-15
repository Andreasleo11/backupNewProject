<?php

namespace App\Livewire\PurchaseRequest;

use App\Application\PurchaseRequest\Queries\PurchaseRequestQueryBuilder;
use App\Application\PurchaseRequest\Queries\GetPurchaseRequestStats;
use App\Application\PurchaseRequest\Queries\Filters\StatusFilter;
use App\Application\PurchaseRequest\Queries\Filters\DepartmentFilter;
use App\Application\PurchaseRequest\Queries\Filters\DateRangeFilter;
use App\Application\PurchaseRequest\Queries\Filters\GlobalSearchFilter;
use App\Application\PurchaseRequest\Queries\Filters\MyApprovalFilter;
use App\Application\PurchaseRequest\Queries\Filters\InReviewFilter;
use App\Application\PurchaseRequest\Queries\Filters\ApprovedThisMonthFilter;
use App\Application\PurchaseRequest\Queries\Filters\MyActiveRequestsFilter;
use App\Application\PurchaseRequest\Queries\Filters\DeptActiveRequestsFilter;
use App\Application\PurchaseRequest\Queries\Filters\DraftsFilter;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use App\Models\PurchaseRequest;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

#[Layout('new.layouts.app')]
class PurchaseRequestIndex extends Component
{
    use WithPagination;

    // View state
    public $activeDrawer = 'insights';

    // Selection
    public array $selectedIds = [];

    // Batch UI
    public bool $showRejectReason = false;
    public string $rejectionReason = '';

    // Batch processing
    public bool $batchProcessing = false;
    public array $processingIds = [];

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


    public function mount()
    {
        // Auto-set 'My Approval' preset for approver roles if no explicit filter is present
        if (!request()->filled('filter') && !request()->filled('preset')) {
            $user = auth()->user();
            if ($user && $user->hasAnyRole(['department-head', 'general-manager', 'verificator', 'director'])) {
                $this->preset = 'my_approval';
            }
        }

        // Inherit preset from URL if provided (legacy support, overrides above)
        if (request()->filled('filter')) {
            $this->preset = request('filter');
        }

        $this->resetPage();
    }

    public function updated($name, $value): void
    {
        if (in_array($name, ['search', 'status', 'department', 'dateRange', 'branch', 'preset', 'perPage', 'sortField', 'sortDirection', 'page'])) {
            if (!in_array($name, ['page'])) { // Don't reset page when page changes
                $this->resetPage();
            }
            $this->selectedIds = [];
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->status = '';
        $this->department = '';
        $this->dateRange = '';
        $this->branch = '';
        $this->preset = 'all';
        $this->resetPage();
    }



    public function clearFilters()
    {
        $this->reset(['search', 'status', 'department', 'dateRange', 'branch', 'preset']);
        $this->resetPage();
        $this->selectedIds = [];
    }

    public function clearFilter(string $filter): void
    {
        match ($filter) {
            'search' => $this->search = '',
            'status' => $this->status = '',
            'department' => $this->department = '',
            'dateRange' => $this->dateRange = '',
            'branch' => $this->branch = '',
            'preset' => $this->preset = 'all',
            default => null,
        };
        $this->resetPage();
        $this->selectedIds = [];
    }

    public function setPreset($preset)
    {
        $this->preset = $preset;
        $this->resetPage();
    }

    public function getActiveFilterCountProperty()
    {
        $count = 0;
        if ($this->status) $count++;
        if ($this->department) $count++;
        if ($this->dateRange) $count++;
        if ($this->branch) $count++;
        return $count;
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['id', 'pr_no', 'doc_num', 'date_pr', 'created_at', 'supplier', 'from_department'], true)) {
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
        $builder = new PurchaseRequestQueryBuilder();
        $user = auth()->user();
        $query = $builder->forUser($user);

        $filters = [];

        if ($this->search) {
            $filters[] = new GlobalSearchFilter($this->search);
        }

        if ($this->status) {
            $filters[] = new StatusFilter($this->status);
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

        if ($this->preset && $this->preset !== 'all') {
            $presetFilter = match ($this->preset) {
                'my_approval'    => new MyApprovalFilter($user),
                'in_review'      => new InReviewFilter(),
                'approved_month' => new ApprovedThisMonthFilter(),
                'my_active'      => new MyActiveRequestsFilter($user),
                'dept_active'    => new DeptActiveRequestsFilter($user),
                'drafts'         => new DraftsFilter($user),
                default          => null,
            };

            if ($presetFilter) {
                $filters[] = $presetFilter;
            }
        }

        // Apply Sorting
        // Validate sort field exists to prevent SQL errors
        $allowedSorts = ['id', 'pr_no', 'doc_num', 'date_pr', 'created_at', 'supplier', 'from_department'];
        $sortColumn = in_array($this->sortField, $allowedSorts) ? $this->sortField : 'id';
        $sortDir = in_array(strtolower($this->sortDirection), ['asc', 'desc']) ? $this->sortDirection : 'desc';
        
        return $builder->withFilters($query, $filters)->orderBy($sortColumn, $sortDir);
    }

    #[Computed]
    public function departments()
    {
        return \App\Enums\ToDepartment::values();
    }

    #[Computed]
    public function statuses()
    {
        return ['DRAFT', 'IN_REVIEW', 'APPROVED', 'REJECTED', 'CANCELED'];
    }

    #[Computed]
    public function branches()
    {
        return \App\Enums\Branch::values();
    }

    #[Computed]
    public function stats(): array
    {
        $builder = new PurchaseRequestQueryBuilder();
        $statsFetcher = new GetPurchaseRequestStats($builder);
        return $statsFetcher->execute();
    }

    public function render()
    {
        $rows = $this->queryRows()->paginate($this->perPage);

        return view('livewire.purchase-request.purchase-request-index', [
            'rows'            => $rows,
            'stats'           => $this->stats,
            'canBatchApprove' => auth()->user()->can('pr.batch-approve'),
            'departments'     => $this->departments,
            'statuses'        => $this->statuses,
            'branches'        => $this->branches,
        ]);
    }

    // --- Actions ---

    public function cancelReject()
    {
        $this->showRejectReason = false;
        $this->rejectionReason = '';
    }

    public function batchApprove(\App\Application\PurchaseRequest\UseCases\BatchApprovePurchaseRequests $useCase)
    {
        if (empty($this->selectedIds)) return;

        if (count($this->selectedIds) > $this->perPage) {
            $this->dispatch('toast', message: "You can only process up to {$this->perPage} items at a time (current page limit).", type: 'error');
            return;
        }

        $this->processingIds = $this->selectedIds;
        $this->batchProcessing = true;
        $this->dispatch('toast', message: 'Processing approvals...', type: 'info');

        sleep(2); // Ensure loading state is visible

        $result = $useCase->handle($this->selectedIds, auth()->id());

        $this->batchProcessing = false;
        $this->processingIds = [];

        if ($result['success']) {
            $this->dispatch('toast', message: "Approved {$result['approved']} purchase requests successfully.", type: 'success');

            // Adjust page if current page is now empty
            $currentPageItems = $this->queryRows()->paginate($this->perPage, ['*'], 'page', $this->getPage());
            if ($currentPageItems->count() == 0 && $this->getPage() > 1) {
                $this->setPage($this->getPage() - 1);
            }
        } else {
            $this->dispatch('toast', message: $result['message'], type: 'error');
        }

            $this->selectedIds = [];

        // Clear stats cache and refresh
        GetPurchaseRequestStats::clearCache(auth()->id());
        $this->dispatch('$refresh');
    }

    public function batchReject(\App\Application\PurchaseRequest\UseCases\BatchRejectPurchaseRequests $useCase)
    {
        if (empty($this->selectedIds)) return;
        if (empty($this->rejectionReason)) {
            $this->dispatch('toast', message: 'Rejection reason is required.', type: 'error');
            return;
        }

        if (count($this->selectedIds) > $this->perPage) {
            $this->dispatch('toast', message: "You can only process up to {$this->perPage} items at a time (current page limit).", type: 'error');
            return;
        }

        $this->processingIds = $this->selectedIds;
        $this->batchProcessing = true;
        $this->dispatch('toast', message: 'Processing rejections...', type: 'info');

        sleep(2); // Ensure loading state is visible

        $result = $useCase->handle($this->selectedIds, auth()->id(), $this->rejectionReason);

        $this->batchProcessing = false;
        $this->processingIds = [];
        $this->showRejectReason = false;
        $this->rejectionReason = '';

        if ($result['success']) {
            $this->dispatch('toast', message: "Rejected {$result['rejected']} purchase requests.", type: 'success');

            // Adjust page if current page is now empty
            $currentPageItems = $this->queryRows()->paginate($this->perPage, ['*'], 'page', $this->getPage());
            if ($currentPageItems->count() == 0 && $this->getPage() > 1) {
                $this->setPage($this->getPage() - 1);
            }
        } else {
            $this->dispatch('toast', message: $result['message'], type: 'error');
        }

            $this->selectedIds = [];

        // Clear stats cache and refresh
        GetPurchaseRequestStats::clearCache(auth()->id());
        $this->dispatch('$refresh');
    }

    #[On('refresh-index')]
    public function refresh()
    {
        // Simply triggers a re-render
    }

    public function refreshTable()
    {
        // Force component refresh for snappy updates
        $this->dispatch('$refresh');
    }




}
