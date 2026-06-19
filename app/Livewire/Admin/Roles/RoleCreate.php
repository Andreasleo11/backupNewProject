<?php

namespace App\Livewire\Admin\Roles;

use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleCreate extends Component
{
    public string $name = '';
    public ?string $description = null;
    public array $selectedPermissions = [];

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],
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
        $this->authorize('role.create');
        $this->validate();

        $role = new Role();
        $role->name = $this->name;
        $role->description = $this->description;
        $role->guard_name = 'web';
        $role->save();

        $role->syncPermissions($this->selectedPermissions);

        session()->flash('success', 'Role created successfully.');
        $this->redirectRoute('admin.roles.index');
    }

    public function render()
    {
        return view('livewire.admin.roles.role-create');
    }
}
