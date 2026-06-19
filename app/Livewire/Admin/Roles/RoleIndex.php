<?php

namespace App\Livewire\Admin\Roles;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class RoleIndex extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    public int $perPage = 10;

    // Delete Guardrail
    public ?int $roleToDeleteId = null;
    public bool $showDeleteModal = false;

    // Bulk selection
    public array $selectedRows = [];
    public bool $selectAll = false;
    public bool $showBulkDeleteModal = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedPage(): void
    {
        $this->clearSelection();
    }

    private function clearSelection(): void
    {
        $this->selectedRows = [];
        $this->selectAll = false;
    }

    public function confirmDelete(int $roleId): void
    {
        $this->authorize('role.delete');

        $role = Role::findOrFail($roleId);

        if ($role->name === 'super-admin') {
            $this->dispatch('toast', type: 'error', message: 'Super admin role cannot be deleted.');
            return;
        }

        $this->roleToDeleteId = $roleId;
        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        if (! $this->roleToDeleteId) {
            return;
        }

        $this->authorize('role.delete');

        $role = Role::find($this->roleToDeleteId);

        if ($role) {
            $role->delete();
            $this->dispatch('toast', type: 'success', message: 'Role deleted successfully.');
        }

        $this->showDeleteModal = false;
        $this->roleToDeleteId = null;
    }

    #[Computed]
    public function roles()
    {
        return Role::query()
            ->withCount('users', 'permissions')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = collect($this->roles->items())->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function confirmBulkDelete(): void
    {
        $this->authorize('role.delete');
        if (empty($this->selectedRows)) return;
        
        $this->showBulkDeleteModal = true;
    }

    public function executeBulkDelete(): void
    {
        $this->authorize('role.delete');
        if (empty($this->selectedRows)) return;

        $roles = Role::whereIn('id', $this->selectedRows)->get();
        
        $deletedCount = 0;
        foreach ($roles as $role) {
            if ($role->name !== 'super-admin') {
                $role->delete();
                $deletedCount++;
            }
        }

        $this->showBulkDeleteModal = false;
        $this->selectedRows = [];
        $this->selectAll = false;

        $this->dispatch('toast', type: 'success', message: "{$deletedCount} roles deleted successfully.");
    }

    public function render()
    {
        return view('livewire.admin.roles.role-index', [
            'roles' => $this->roles,
        ]);
    }
}
