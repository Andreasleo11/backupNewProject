<?php

namespace App\Livewire\Admin\Roles;

use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleIndex extends Component
{
    public string $search = '';

    public bool $showModal = false;

    public string $modalMode = 'create'; // 'create' | 'edit'

    public ?int $editingRoleId = null;

    public string $name = '';

    public array $selectedPermissions = [];

    public function getRolesProperty()
    {
        return Role::query()
            ->with('permissions')
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name')
            ->get();
    }

    public function getPermissionsProperty()
    {
        return Permission::orderBy('name')->get();
    }

    public function openCreateModal(): void
    {
        $this->authorize('role.create');

        $this->reset(['editingRoleId', 'name', 'selectedPermissions']);
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function openEditModal(int $roleId): void
    {
        $this->authorize('role.update');

        $role = Role::with('permissions')->findOrFail($roleId);

        $this->editingRoleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();

        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function updatedShowModal($value)
    {
        if (! $value) {
            $this->reset('name', 'selectedPermissions', 'editingRoleId', 'modalMode');
            $this->resetValidation();
        }
    }

    public function save(): void
    {
        if ($this->modalMode === 'create') {
            $this->authorize('role.create');
        } else {
            $this->authorize('role.update');
        }

        $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')->ignore($this->editingRoleId),
            ],
            'selectedPermissions' => 'array',
        ]);

        if ($this->modalMode === 'create') {
            $role = Role::create(['name' => $this->name]);
        } else {
            $role = Role::findOrFail($this->editingRoleId);
            $role->update(['name' => $this->name]);
        }

        // sync permissions
        $role->syncPermissions($this->selectedPermissions);

        $this->showModal = false;

        $this->dispatch('flash', type: 'success', message: $this->modalMode === 'create' ? 'Role created successfully.' : 'Role updated successfully.');

        $this->reset(['editingRoleId', 'name', 'selectedPermissions', 'modalMode']);
        $this->modalMode = 'create';
    }

    public function confirmDelete(int $roleId): void
    {
        $this->authorize('role.delete');

        $role = Role::findOrFail($roleId);

        if ($role->name === 'super-admin') {
            $this->dispatch('flash', type: 'error', message: 'Super admin role cannot be deleted.');

            return;
        }

        $role->delete();

        $this->dispatch('flash', type: 'success', message: 'Role deleted successfully.');
    }

    public function render()
    {
        return view('livewire.admin.roles.role-index', [
            'roles' => $this->roles,
            'permissions' => $this->permissions,
        ]);
    }
}
