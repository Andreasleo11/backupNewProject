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

    // ── Computed Properties ────────────────────────────────────────────────────

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

    /**
     * Returns permissions grouped by module for the UI matrix.
     * Each group key maps to an array of Permission objects.
     */
    public function getGroupedPermissionsProperty(): array
    {
        $all = Permission::orderBy('name')->get();
        $groups = [];
        $used = [];

        foreach (config('permission_groups.groups', []) as $label => $prefixes) {
            $prefixes = (array) $prefixes;
            $matched = $all->filter(
                fn ($p) => collect($prefixes)->contains(fn ($px) => str_starts_with($p->name, $px))
            );
            if ($matched->isNotEmpty()) {
                $groups[$label] = $matched->values();
                $used = array_merge($used, $matched->pluck('name')->toArray());
            }
        }

        // Catch-all: any permissions not matched by any group
        $other = $all->whereNotIn('name', $used);
        if ($other->isNotEmpty()) {
            $groups['Other'] = $other->values();
        }

        return $groups;
    }

    /**
     * Toggle all permissions within a named group.
     * If all are already selected → deselect. Otherwise → select all.
     */
    public function toggleGroup(string $label): void
    {
        $group = $this->groupedPermissions[$label] ?? collect();
        $names = collect($group)->pluck('name')->toArray();
        $allChosen = collect($names)->every(fn ($n) => in_array($n, $this->selectedPermissions));

        if ($allChosen) {
            $this->selectedPermissions = array_values(
                array_diff($this->selectedPermissions, $names)
            );
        } else {
            $this->selectedPermissions = array_values(
                array_unique(array_merge($this->selectedPermissions, $names))
            );
        }
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

        $this->dispatch('toast', type: 'success', message: $this->modalMode === 'create' ? 'Role created successfully.' : 'Role updated successfully.');

        $this->reset(['editingRoleId', 'name', 'selectedPermissions', 'modalMode']);
        $this->modalMode = 'create';
    }

    public function confirmDelete(int $roleId): void
    {
        $this->authorize('role.delete');

        $role = Role::findOrFail($roleId);

        if ($role->name === 'super-admin') {
            $this->dispatch('toast', type: 'error', message: 'Super admin role cannot be deleted.');

            return;
        }

        $role->delete();

        $this->dispatch('toast', type: 'success', message: 'Role deleted successfully.');
    }

    public function render()
    {
        return view('livewire.admin.roles.role-index', [
            'roles' => $this->roles,
            'permissions' => $this->permissions,
            'groupedPermissions' => $this->groupedPermissions,
        ])->layout('new.layouts.app');
    }
}
