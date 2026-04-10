<?php

namespace App\Livewire\Admin\Employees;

use App\Application\Employee\DTOs\EmployeeFilter;
use App\Application\Employee\UseCases\ListEmployees;
use App\Models\ImportJob;
use Livewire\Component;
use Livewire\WithPagination;

use Livewire\Attributes\Computed;

class EmployeeIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $branch = '';
    public ?string $deptCode = '';
    public ?string $employmentType = '';
    public int $perPage = 10;
    public bool $showAdvancedFilters = false;

    public string $sortBy = 'name';
    public string $sortDirection = 'asc';

    public ?array $previewData = null;
    public ?array $activeLog = null;
    public string $previewTab = 'summary';
    public string $previewSearch = '';

    public ?string $selectedNik = null;
    public ?\App\Infrastructure\Persistence\Eloquent\Models\Employee $selectedEmployee = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'branch' => ['except' => ''],
        'deptCode' => ['except' => ''],
        'employmentType' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBranch(): void
    {
        $this->resetPage();
    }

    public function updatedDeptCode(): void
    {
        $this->resetPage();
    }

    public function updatedEmploymentType(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'branch', 'deptCode', 'employmentType']);
        $this->resetPage();
    }

    public function sort_by(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function openAudit(string $nik): void
    {
        $this->selectedNik = $nik;
        $this->selectedEmployee = \App\Infrastructure\Persistence\Eloquent\Models\Employee::where('nik', $nik)
            ->with(['evaluationData', 'warningLogs', 'latestDailyReport', 'department'])
            ->first();
    }

    public function closeAudit(): void
    {
        $this->selectedNik = null;
        $this->selectedEmployee = null;
    }

    public function sync(\App\Services\JPayrollService $service): void
    {
        $result = $service->previewSync('10000', now()->year);
        if ($result['success']) {
            $this->previewData = $result;
            $this->previewTab = 'summary';
            $this->previewSearch = '';
        } else {
            session()->flash('error', 'Preview failed: ' . $result['message']);
        }
    }

    public function confirmSync(): void
    {
        $log = \App\Models\ImportJob::create([
            'type' => 'jpayroll_sync',
            'status' => 'running',
            'started_at' => now(),
            'results_snapshot' => $this->previewData,
            'total_rows' => ($this->previewData['summary']['new'] ?? 0) + ($this->previewData['summary']['updated'] ?? 0),
        ]);

        \App\Jobs\SyncEmployeesJob::dispatch('10000', now()->year, $log->id);
        $this->previewData = null;
        session()->flash('success', 'Sync job dispatched.');
    }

    public function viewLog(int $id): void
    {
        $log = \App\Models\ImportJob::find($id);
        if ($log && $log->results_snapshot) {
            $this->activeLog = $log->results_snapshot;
            $this->previewTab = 'summary';
            $this->previewSearch = '';
        }
    }

    public function closeLog(): void
    {
        $this->activeLog = null;
    }

    public function cancelSync(): void
    {
        $this->previewData = null;
    }

    public function sort_icon(string $field): string
    {
        if ($this->sortBy !== $field) return '⇅';
        return $this->sortDirection === 'asc' ? '↑' : '↓';
    }

    #[Computed]
    public function employees()
    {
        $listEmployees = app(ListEmployees::class);
        $filter = new EmployeeFilter(
            search: $this->search,
            perPage: $this->perPage,
            sortBy: $this->sortBy,
            sortDirection: $this->sortDirection,
            branch: $this->branch ?: null,
            deptCode: $this->deptCode ?: null,
            employmentType: $this->employmentType ?: null,
        );

        return $listEmployees->execute($filter);
    }

    #[Computed]
    public function globalStats(): array
    {
        $repo = app(\App\Domain\Employee\Repositories\EmployeeRepository::class);
        return $repo->getGlobalStats();
    }

    #[Computed]
    public function recentSyncs()
    {
        return \App\Models\ImportJob::where('type', 'jpayroll_sync')
            ->latest('started_at')
            ->take(5)
            ->get();
    }

    #[Computed]
    public function availableBranches()
    {
        return \App\Infrastructure\Persistence\Eloquent\Models\Employee::distinct()
            ->whereNotNull('branch')
            ->orderBy('branch')
            ->pluck('branch');
    }

    #[Computed]
    public function availableDepartments()
    {
        return \App\Infrastructure\Persistence\Eloquent\Models\Department::orderBy('name')->get();
    }

    #[Computed]
    public function availableEmploymentTypes()
    {
        return \App\Infrastructure\Persistence\Eloquent\Models\Employee::distinct()
            ->whereNotNull('employment_type')
            ->orderBy('employment_type')
            ->pluck('employment_type');
    }

    public function render()
    {
        return view('admin.employees.index')->layout('new.layouts.app');
    }
}
