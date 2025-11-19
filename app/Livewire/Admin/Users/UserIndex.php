<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UserIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $search = '';

    public bool $showModal = false;
    public ?int $editingUserId = null;
    public array $selectedRoles = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openRoleModal(int $userId): void
    {
        $user = User::with('roles')->findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->showModal = true;
    }

    public function saveRoles(): void
    {
        $this->validate([
            'selectedRoles' => 'array',
        ]);

        $user = User::findOrFail($this->editingUserId);

        // sync roles by name
        $user->syncRoles($this->selectedRoles);

        $this->showModal = false;
        session()->flash('success', 'User roles updated successfully.');

        $this->reset(['editingUserId', 'selectedRoles']);
    }

    public function render()
    {
        $users = User::query()
            ->with('roles')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        $roles = Role::orderBy('name')->get();

        return view('livewire.admin.users.index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}
