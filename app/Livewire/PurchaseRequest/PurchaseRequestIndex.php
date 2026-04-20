<?php

namespace App\Livewire\PurchaseRequest;

use App\Application\PurchaseRequest\Queries\Filters\ApprovedThisMonthFilter;
use App\Application\PurchaseRequest\Queries\Filters\DateRangeFilter;
use App\Application\PurchaseRequest\Queries\Filters\DepartmentFilter;
use App\Application\PurchaseRequest\Queries\Filters\DeptActiveRequestsFilter;
use App\Application\PurchaseRequest\Queries\Filters\DraftsFilter;
use App\Application\PurchaseRequest\Queries\Filters\GlobalSearchFilter;
use App\Application\PurchaseRequest\Queries\Filters\InReviewFilter;
use App\Application\PurchaseRequest\Queries\Filters\MyActiveRequestsFilter;
use App\Application\PurchaseRequest\Queries\Filters\MyApprovalFilter;
use App\Application\PurchaseRequest\Queries\Filters\StatusFilter;
use App\Application\PurchaseRequest\Queries\GetPurchaseRequestStats;
use App\Application\PurchaseRequest\Queries\PurchaseRequestQueryBuilder;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

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

    // Allowed sort columns — centralised so sortBy() and queryRows() stay in sync
    private const ALLOWED_SORTS = ['id', 'pr_no', 'doc_num', 'date_pr', 'created_at', 'supplier', 'from_department', 'po_number'];

    public function mount(): void
    {
        // Auto-set 'My Approval' preset for approver roles if no explicit preset in URL
        if (! request()->filled('filter') && ! request()->filled('preset')) {
            $user = auth()->user();
            if ($user && $user->hasAnyRole(['department-head', 'general-manager', 'verificator', 'director'])) {
                $this->preset = 'my_approval';
            }
        }

        // Legacy: inherit preset from ?filter= query param
        if (request()->filled('filter')) {
            $this->preset = request('filter');
        }

        $this->resetPage();
    }

    // FIX: updated() used `!in_array($name, ['page'])` which always returns true
    // because in_array expects an array as second arg. Fixed to strict string compare.
    public function updatedPage(): void
    {
        $this->resetSelections();
    }

    public function updated(string $name): void
    {
        $filterProps = ['search', 'status', 'department', 'dateRange', 'branch', 'preset', 'perPage', 'sortField', 'sortDirection'];

        if (in_array($name, $filterProps, true)) {
            $this->resetPage();
            $this->resetSelections();
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->department = '';
        $this->dateRange = '';
        $this->branch = '';
        $this->preset = 'all';
        $this->resetPage();
        $this->resetSelections();
    }

    public function clearFilters(): void
    {
        $this->resetFilters();
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
        $this->resetSelections();
    }

    public function setPreset(string $preset): void
    {
        $this->preset = $preset;
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
                'my_approval' => new MyApprovalFilter($user),
                'in_review' => new InReviewFilter,
                'approved_month' => new ApprovedThisMonthFilter,
                'my_active' => new MyActiveRequestsFilter($user),
                'dept_active' => new DeptActiveRequestsFilter($user),
                'drafts' => new DraftsFilter($user),
                default => null,
            };

            if ($presetFilter) {
                $filters[] = $presetFilter;
            }
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
        return ['DRAFT', 'IN_REVIEW', 'APPROVED', 'REJECTED', 'CANCELED'];
    }

    #[Computed]
    public function branches(): array
    {
        return \App\Enums\Branch::values();
    }

    #[Computed]
    public function stats(): array
    {
        $builder = new PurchaseRequestQueryBuilder;
        $statsFetcher = new GetPurchaseRequestStats($builder);

        return $statsFetcher->execute();
    }

    private function resetSelections(): void
    {
        $this->selectedIds = [];
        $this->dispatch('reset-selections');
    }

    public function render()
    {
        $rows = $this->queryRows()->paginate($this->perPage);

        return view('livewire.purchase-request.purchase-request-index', [
            'rows' => $rows,
            'stats' => $this->stats,
            'canBatchApprove' => auth()->user()->can('pr.batch-approve'),
            'departments' => $this->departments,
            'statuses' => $this->statuses,
            'branches' => $this->branches,
        ]);
    }

    // ── Actions ──────────────────────────────────────────────────────────────

    public function cancelReject(): void
    {
        $this->showRejectReason = false;
        $this->rejectionReason = '';
    }

    public function batchApprove(
        \App\Application\PurchaseRequest\UseCases\BatchApprovePurchaseRequests $useCase,
        array $selectedIds = []
    ): void {
        if (! empty($selectedIds)) {
            $this->selectedIds = $selectedIds;
        }

        if (empty($this->selectedIds)) {
            return;
        }

        if (count($this->selectedIds) > $this->perPage) {
            $this->dispatch('toast', message: "You can only process up to {$this->perPage} items at a time.", type: 'error');

            return;
        }

        $ids = $this->selectedIds;

        // Reset UI state before the blocking operation so the next render reflects it
        $this->selectedIds = [];
        $this->processingIds = $ids;
        $this->batchProcessing = true;

        // FIX: Removed sleep(2) — it blocks PHP and prevents Livewire from
        // streaming the loading state. The spinner/notification is driven by
        // batchProcessing=true which the browser will see on the next network
        // response, so a sleep here only makes the UX slower, not better.

        $result = $useCase->handle($ids, auth()->id());

        $this->batchProcessing = false;
        $this->processingIds = [];

        if ($result['success']) {
            $this->dispatch('toast', message: "Approved {$result['approved']} purchase request(s) successfully.", type: 'success');
            $this->adjustPageIfEmpty();
        } else {
            $this->dispatch('toast', message: $result['message'], type: 'error');
        }

        GetPurchaseRequestStats::clearCache(auth()->id());
    }

    public function batchReject(
        \App\Application\PurchaseRequest\UseCases\BatchRejectPurchaseRequests $useCase,
        array $selectedIds = []
    ): void {
        if (! empty($selectedIds)) {
            $this->selectedIds = $selectedIds;
        }

        if (empty($this->selectedIds)) {
            return;
        }

        if (empty($this->rejectionReason)) {
            $this->dispatch('toast', message: 'Rejection reason is required.', type: 'error');

            return;
        }

        if (count($this->selectedIds) > $this->perPage) {
            $this->dispatch('toast', message: "You can only process up to {$this->perPage} items at a time.", type: 'error');

            return;
        }

        $ids = $this->selectedIds;

        $this->selectedIds = [];
        $this->processingIds = $ids;
        $this->batchProcessing = true;

        // FIX: Removed sleep(2) — see note in batchApprove above.

        $result = $useCase->handle($ids, auth()->id(), $this->rejectionReason);

        $this->batchProcessing = false;
        $this->processingIds = [];
        $this->showRejectReason = false;
        $this->rejectionReason = '';

        if ($result['success']) {
            // FIX: was incorrectly saying "Approved" in the rejection success toast.
            $this->dispatch('toast', message: "Rejected {$result['rejected']} purchase request(s) successfully.", type: 'success');
            $this->adjustPageIfEmpty();
        } else {
            $this->dispatch('toast', message: $result['message'], type: 'error');
        }

        GetPurchaseRequestStats::clearCache(auth()->id());
    }

    /**
     * After a batch action removes rows, step back one page if the current
     * page is now empty. Extracted to avoid duplicating the query twice.
     */
    private function adjustPageIfEmpty(): void
    {
        $currentPage = $this->getPage();
        if ($currentPage <= 1) {
            return;
        }

        $count = $this->queryRows()->paginate($this->perPage, ['*'], 'page', $currentPage)->count();
        if ($count === 0) {
            $this->setPage($currentPage - 1);
        }
    }

    #[On('refresh-index')]
    public function refresh(): void
    {
        // Clear stats cache since approval/rejection changes stats
        GetPurchaseRequestStats::clearCache(auth()->id());
        // Force re-render of the component
        $this->dispatch('$refresh');
    }

    public function refreshTable(): void
    {
        $this->dispatch('$refresh');
    }

    public function updatePoNumber(int $id, string $poNumber): void
    {
        Validator::make(
            ['editingPoNumber' => $poNumber],
            ['editingPoNumber' => 'nullable|string|max:255']
        )->validate();

        try {
            $dto = new \App\Application\PurchaseRequest\DTOs\UpdatePoNumberDTO(
                purchaseRequestId: (int) $id,
                poNumber: $poNumber,
                updatedByUserId: (int) auth()->id()
            );

            $useCase = app(\App\Application\PurchaseRequest\UseCases\UpdatePoNumber::class);
            $useCase->handle($dto);

            $this->dispatch('toast', message: 'PO Number updated successfully!', type: 'success');
            $this->dispatch('close-edit-po-modal');

            GetPurchaseRequestStats::clearCache(auth()->id());
            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }
}
