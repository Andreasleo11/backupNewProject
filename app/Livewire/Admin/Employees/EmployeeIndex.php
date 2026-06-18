<?php

namespace App\Livewire\Admin\Employees;

use App\Application\Employee\DTOs\EmployeeFilter;
use App\Application\Employee\UseCases\ListEmployees;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeIndex extends Component
{
    use WithPagination;

    // ── Filters ──────────────────────────────────────────────────────────────
    public string $search = '';
    public string $branch = '';
    public ?string $deptCode = '';
    public ?string $employmentType = '';
    public int $perPage = 10;
    public bool $showAdvancedFilters = false;
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';

    // ── Sync picker state ─────────────────────────────────────────────────────
    /** Which phases the user has ticked. Defaults to employees only. */
    public array $syncPhases = ['employees'];

    /** ISO date strings for the attendance phase range. */
    public string $syncFromDate = '';
    public string $syncToDate = '';

    // ── Preview / log modal state ──────────────────────────────────────────────
    public ?array $previewData = null;
    public ?array $activeLog = null;
    public string $previewPhase = 'employees';
    public string $previewTab = 'summary';
    public string $previewSearch = '';

    // ── Employee audit drawer ─────────────────────────────────────────────────
    public ?string $selectedNik = null;
    public ?\App\Infrastructure\Persistence\Eloquent\Models\Employee $selectedEmployee = null;

    protected $queryString = [
        'search'         => ['except' => ''],
        'branch'         => ['except' => ''],
        'deptCode'       => ['except' => ''],
        'employmentType' => ['except' => ''],
    ];

    // ── Filter handlers ───────────────────────────────────────────────────────
    public function updatedSearch(): void          { $this->resetPage(); }
    public function updatedBranch(): void          { $this->resetPage(); }
    public function updatedDeptCode(): void        { $this->resetPage(); }
    public function updatedEmploymentType(): void  { $this->resetPage(); }

    public function resetFilters(): void
    {
        $this->reset(['search', 'branch', 'deptCode', 'employmentType']);
        $this->resetPage();
    }

    // ── Sort ──────────────────────────────────────────────────────────────────
    public function sort_by(string $column): void
    {
        $this->sortDirection = $this->sortBy === $column
            ? ($this->sortDirection === 'asc' ? 'desc' : 'asc')
            : 'asc';

        $this->sortBy = $column;
        $this->resetPage();
    }

    public function sort_icon(string $field): string
    {
        if ($this->sortBy !== $field) return '⇅';
        return $this->sortDirection === 'asc' ? '↑' : '↓';
    }

    // ── Audit drawer ──────────────────────────────────────────────────────────
    public function openAudit(string $nik): void
    {
        $this->selectedNik      = $nik;
        $this->selectedEmployee = \App\Infrastructure\Persistence\Eloquent\Models\Employee::where('nik', $nik)
            ->with(['evaluationData', 'warningLogs', 'latestDailyReport', 'department'])
            ->first();
    }

    public function closeAudit(): void
    {
        $this->selectedNik      = null;
        $this->selectedEmployee = null;
    }

    // ── Sync flow ─────────────────────────────────────────────────────────────

    /**
     * Kick off a preview fetch for the selected phases.
     * Called when the user clicks "Preview" in the sync panel.
     */
    public function sync(\App\Services\JPayrollService $service): void
    {
        if (empty($this->syncPhases)) {
            session()->flash('error', 'Please select at least one phase to sync.');
            return;
        }

        $result = $service->previewSync(
            companyArea: '10000',
            year:        now()->year,
            phases:      $this->syncPhases,
            fromDate:    $this->syncFromDate ?: null,
            toDate:      $this->syncToDate   ?: null,
        );

        if ($result['success']) {
            $this->previewData = $result;
            $this->previewPhase = $this->syncPhases[0] ?? 'employees';
            $this->previewTab  = 'summary';
            $this->previewSearch = '';
        } else {
            session()->flash('error', 'Preview failed: ' . $result['message']);
        }
    }

    /**
     * Dispatch the background job for the user-selected phases.
     */
    public function confirmSync(): void
    {
        $log = \App\Models\ImportJob::create([
            'type'              => 'jpayroll_sync',
            'status'            => 'running',
            'started_at'        => now(),
            'results_snapshot'  => $this->previewData,
            'total_rows'        => ($this->previewData['employees']['summary']['new'] ?? 0)
                                 + ($this->previewData['employees']['summary']['updated'] ?? 0),
        ]);

        \App\Jobs\PayrollSyncJob::dispatch(
            '10000',
            now()->year,
            $this->syncPhases,
            $this->syncFromDate ?: null,
            $this->syncToDate   ?: null,
            $log->id,
        );

        $this->previewData = null;
        session()->flash('success', 'Sync job dispatched for: ' . implode(', ', $this->syncPhases));
    }

    public function cancelSync(): void
    {
        $this->previewData = null;
    }

    // ── Log viewer ────────────────────────────────────────────────────────────
    public function viewLog(int $id): void
    {
        $log = \App\Models\ImportJob::find($id);
        if ($log && $log->results_snapshot) {
            $this->activeLog   = $log->results_snapshot;
            $phases = $this->activeLog['phases'] ?? ['employees'];
            $this->previewPhase = $phases[0] ?? 'employees';
            $this->previewTab  = 'summary';
            $this->previewSearch = '';
        }
    }

    public function closeLog(): void
    {
        $this->activeLog = null;
    }

    // ── Computed properties ───────────────────────────────────────────────────
    #[Computed]
    public function employees()
    {
        $filter = new EmployeeFilter(
            search:          $this->search,
            perPage:         $this->perPage,
            sortBy:          $this->sortBy,
            sortDirection:   $this->sortDirection,
            branch:          $this->branch ?: null,
            deptCode:        $this->deptCode ?: null,
            employmentType:  $this->employmentType ?: null,
        );

        return app(ListEmployees::class)->execute($filter);
    }

    #[Computed]
    public function globalStats(): array
    {
        return app(\App\Domain\Employee\Repositories\EmployeeRepository::class)->getGlobalStats();
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
