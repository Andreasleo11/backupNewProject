<?php

namespace App\Livewire\Admin\Roles;

use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleEdit extends Component
{
    public int $editingRoleId;
    public string $name = '';
    public ?string $description = null;
    public array $selectedPermissions = [];

    public function mount(int $roleId): void
    {
        $role = Role::with('permissions')->findOrFail($roleId);
        $this->editingRoleId = $role->id;
        $this->name = $role->name;
        $this->description = $role->description ?? null;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('roles', 'name')->ignore($this->editingRoleId)
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'selectedPermissions' => ['array'],
        ];
    }

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

        $other = $all->whereNotIn('name', $used);
        if ($other->isNotEmpty()) {
            $groups['Other'] = $other->values();
        }

        return $groups;
    }

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

    public function save(): void
    {
        $this->authorize('role.update');
        $this->validate();

        $role = Role::findOrFail($this->editingRoleId);
        $role->name = $this->name;
        $role->description = $this->description;
        $role->save();

        $role->syncPermissions($this->selectedPermissions);

        session()->flash('success', 'Role updated successfully.');
        $this->redirectRoute('admin.roles.index');
    }

    public function render()
    {
        return view('livewire.admin.roles.role-edit');
    }
}
