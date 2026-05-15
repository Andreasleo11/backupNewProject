<?php

namespace App\Livewire\Locker;

use App\Models\Locker;
use App\Models\LockerAssignment;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class LockerDashboard extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    
    // Assignment Properties
    public bool $isAssignModalOpen = false;
    public ?int $selectedLockerId = null;
    public ?string $selectedEmployeeNik = null;
    public string $employeeSearch = '';
    public string $notes = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openAssignModal(int $lockerId): void
    {
        $this->selectedLockerId = $lockerId;
        $this->selectedEmployeeNik = null;
        $this->employeeSearch = '';
        $this->notes = '';
        $this->isAssignModalOpen = true;
    }

    public function assign(): void
    {
        $this->validate([
            'selectedEmployeeNik' => 'required|exists:employees,nik',
            'selectedLockerId' => 'required|exists:lockers,id',
        ], [
            'selectedEmployeeNik.required' => 'Please select an employee.',
        ]);

        $locker = Locker::findOrFail($this->selectedLockerId);
        
        if ($locker->status === 'occupied') {
            $this->dispatch('toast', message: 'Locker is already occupied', type: 'error');
            return;
        }

        LockerAssignment::create([
            'locker_id' => $locker->id,
            'employee_id' => $this->selectedEmployeeNik,
            'assigned_at' => now(),
            'notes' => $this->notes,
        ]);

        $locker->update(['status' => 'occupied']);

        $this->isAssignModalOpen = false;
        $this->dispatch('toast', message: 'Locker assigned successfully', type: 'success');
    }

    public function release(int $lockerId): void
    {
        $locker = Locker::findOrFail($lockerId);
        $assignment = $locker->currentAssignment;

        if ($assignment) {
            $assignment->update(['released_at' => now()]);
        }

        $locker->update(['status' => 'available']);
        $this->dispatch('toast', message: 'Locker released successfully', type: 'success');
    }

    #[Computed]
    public function lockers()
    {
        return Locker::query()
            ->with(['currentAssignment.employee'])
            ->when($this->search, fn($q) => $q->where('locker_number', 'like', '%' . $this->search . '%')
                ->orWhere('location', 'like', '%' . $this->search . '%'))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy('locker_number')
            ->paginate(12);
    }

    #[Computed]
    public function employees()
    {
        if (strlen($this->employeeSearch) < 2) {
            return collect();
        }

        return Employee::query()
            ->where(function($q) {
                $q->where('name', 'like', '%' . $this->employeeSearch . '%')
                  ->orWhere('nik', 'like', '%' . $this->employeeSearch . '%');
            })
            ->limit(5)
            ->get();
    }

    public function selectEmployee(string $nik): void
    {
        $this->selectedEmployeeNik = $nik;
        $employee = Employee::where('nik', $nik)->first();
        if ($employee) {
            $this->employeeSearch = $employee->name . ' (' . $employee->nik . ')';
        }
    }

    public function render()
    {
        return view('livewire.locker.locker-dashboard')->layout('new.layouts.app');
    }
}
