<?php

namespace App\Livewire\MonthlyBudget;

use App\Domain\MonthlyBudget\Services\BudgetApprovalService;
use App\Models\MonthlyBudgetReport as Report;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url(as: 'q', keep: true)]
    public ?string $search = null;

    #[Url(keep: true)]
    public ?string $status = null;

    #[Url(as: 'dept', keep: true)]
    public ?string $departmentId = null;

    #[Url(keep: true)]
    public string $sortField = 'created_at';

    #[Url(keep: true)]
    public string $sortDirection = 'desc';

    #[Url(keep: true)]
    public int $perPage = 10;

    public bool $showCreateButton = false;

    private array $sortable = [
        'doc_num' => 'doc_num',
        'report_date' => 'report_date',
        'created_at' => 'created_at',
        'department' => 'dept_no',
    ];

    public function mount(): void
    {
        $u = auth()->user();
        $this->showCreateButton =
            ! $u->is_head && ! $u->is_gm && $u->department?->name !== 'MANAGEMENT';
    }

    public function updating($name): void
    {
        if (in_array($name, ['search', 'status', 'departmentId', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function sortBy(string $field): void
    {
        if (! isset($this->sortable[$field])) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = null;
        $this->status = null;
        $this->departmentId = null;
        $this->resetPage();
    }

    public function render(BudgetApprovalService $approvalService)
    {
        $user = auth()->user();
        $query = $approvalService->getFilteredReportsQuery($user);

        // Keywords
        if ($this->search) {
            $like = '%' . trim($this->search) . '%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('doc_num', 'like', $like)
                  ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', $like));
            });
        }

        // Status filter (Custom handling for Draft vs Approval System)
        if ($this->status !== null && $this->status !== '') {
            if ($this->status === 'DRAFT') {
                $query->whereDoesntHave('approvalRequest');
            } else {
                $query->whereHas('approvalRequest', fn ($q) => $q->where('status', $this->status));
            }
        }

        // Department filter
        if ($this->departmentId) {
            $query->whereHas('department', fn ($q) => $q->where('id', $this->departmentId));
        }

        // Sorting
        $column = $this->sortable[$this->sortField] ?? 'created_at';
        $query->orderBy($column, $this->sortDirection)->orderBy('id', 'desc');

        return view('livewire.monthly-budget.index', [
            'reports' => $query->paginate($this->perPage),
            'departments' => Department::all(),
            'authUser' => $user,
        ]);
    }
}
