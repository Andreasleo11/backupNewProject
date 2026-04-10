<?php

namespace App\Livewire\Admin\Employees;

use App\Application\Employee\DTOs\EmployeeFilter;
use App\Application\Employee\UseCases\ListEmployees;
use App\Models\ImportJob;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $branch = '';
    public int $perPage = 10;

    public string $sortBy = 'nik';          // default sort column

    public string $sortDirection = 'asc';   // default sort direction

    public ?array $previewData = null;      // Holds {summary: [], details: []}
    public ?array $activeLog = null;        // Holds a historical sync log
    public string $previewTab = 'summary';  // Current view in sync modal
    public string $previewSearch = '';     // Filter for drilldown lists

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBranch(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'branch', 'perPage']);
        $this->resetPage();
    }

    public function sort_by($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
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
        // Create an audit log record with the final snapshot
        $log = \App\Models\ImportJob::create([
            'type' => 'jpayroll_sync',
            'status' => 'running',
            'started_at' => now(),
            'results_snapshot' => $this->previewData,
            'total_rows' => ($this->previewData['summary']['new'] ?? 0) + ($this->previewData['summary']['updated'] ?? 0),
        ]);

        \App\Jobs\SyncEmployeesJob::dispatch('10000', now()->year, $log->id);

        $this->previewData = null;
        session()->flash('success', 'Sync job dispatched and will be updated in the background.');
    }

    public function viewLog($id): void
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

    public function render(ListEmployees $listEmployees)
    {
        $filter = new EmployeeFilter(
            search: $this->search,
            perPage: $this->perPage,
            sortBy: $this->sortBy,
            sortDirection: $this->sortDirection,
        );

        $employees = $listEmployees->execute($filter);

        $recentSyncs = ImportJob::where('type', 'jpayroll_sync')
            ->latest('started_at')
            ->take(5)
            ->get();

        return view('admin.employees.index', [
            'employees' => $employees,
            'recentSyncs' => $recentSyncs,
        ])->layout('new.layouts.app');
    }
}
