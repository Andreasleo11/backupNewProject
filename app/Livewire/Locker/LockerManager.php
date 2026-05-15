<?php

namespace App\Livewire\Locker;

use App\Models\Locker;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class LockerManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $sortBy = 'locker_number';
    public string $sortDirection = 'asc';

    // Form properties
    public bool $isModalOpen = false;
    public ?int $editingId = null;
    public string $locker_number = '';
    public string $location = '';
    public string $status = 'available';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isModalOpen = true;
    }

    public function openEditModal(int $id): void
    {
        $locker = Locker::findOrFail($id);
        $this->editingId = $locker->id;
        $this->locker_number = $locker->locker_number;
        $this->location = $locker->location ?? '';
        $this->status = $locker->status;
        $this->isModalOpen = true;
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->locker_number = '';
        $this->location = '';
        $this->status = 'available';
    }

    public function save(): void
    {
        $rules = [
            'locker_number' => 'required|string|unique:lockers,locker_number,' . $this->editingId,
            'location' => 'nullable|string',
            'status' => 'required|in:available,occupied,maintenance',
        ];

        $this->validate($rules);

        if ($this->editingId) {
            $locker = Locker::findOrFail($this->editingId);
            $locker->update([
                'locker_number' => $this->locker_number,
                'location' => $this->location,
                'status' => $this->status,
            ]);
            $this->dispatch('toast', message: 'Locker updated successfully', type: 'success');
        } else {
            Locker::create([
                'locker_number' => $this->locker_number,
                'location' => $this->location,
                'status' => $this->status,
            ]);
            $this->dispatch('toast', message: 'Locker created successfully', type: 'success');
        }

        $this->isModalOpen = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $locker = Locker::findOrFail($id);
        if ($locker->status === 'occupied') {
            $this->dispatch('toast', message: 'Cannot delete an occupied locker', type: 'error');
            return;
        }
        $locker->delete();
        $this->dispatch('toast', message: 'Locker deleted successfully', type: 'success');
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

    #[Computed]
    public function lockers()
    {
        return Locker::query()
            ->when($this->search, fn($q) => $q->where('locker_number', 'like', '%' . $this->search . '%')
                ->orWhere('location', 'like', '%' . $this->search . '%'))
            ->when($this->statusFilter, fn($q) => $q->where('status', $this->statusFilter))
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.locker.locker-manager')->layout('new.layouts.app');
    }
}
