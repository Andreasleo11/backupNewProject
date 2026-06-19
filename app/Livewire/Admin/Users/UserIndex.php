<?php

namespace App\Livewire\Admin\Users;

use App\Application\User\DTOs\UserFilter;
use App\Application\User\UseCases\ChangeUserPassword;
use App\Application\User\UseCases\ListUsersWithEmployees;
use App\Application\User\UseCases\ToggleUserStatus;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;

    // Filters
    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public bool $onlyActive = false;

    #[Url(history: true)]
    public int $perPage = 10;

    // Suspend Guardrail
    public ?int $userToSuspendId = null;
    public bool $showSuspendModal = false;

    // Password Reset Guardrail
    public ?int $passwordUserId = null;
    public bool $showPasswordModal = false;
    public string $newPassword = '';
    public string $newPassword_confirmation = '';

    // Bulk selection
    public array $selectedRows = [];
    public bool $selectAll = false;

    // Bulk Role Assignment
    public bool $showBulkRoleModal = false;
    public string $bulkRoleToAssign = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedOnlyActive(): void
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

    public function toggleStatus(int $userId, ToggleUserStatus $toggleUserStatus): void
    {
        $this->authorize('user.update');
        $toggleUserStatus->execute($userId);
        $this->dispatch('toast', message: 'User status updated successfully.', type: 'success');
    }

    public function confirmSuspend(int $userId): void
    {
        $this->userToSuspendId = $userId;
        $this->showSuspendModal = true;
    }

    public function executeSuspend(ToggleUserStatus $toggleUserStatus): void
    {
        if ($this->userToSuspendId) {
            $this->toggleStatus($this->userToSuspendId, $toggleUserStatus);
            $this->showSuspendModal = false;
            $this->userToSuspendId = null;
        }
    }

    public function openPasswordModal(int $userId): void
    {
        $this->authorize('user.update');
        $this->passwordUserId = $userId;
        $this->newPassword = '';
        $this->newPassword_confirmation = '';
        $this->showPasswordModal = true;
    }

    protected function passwordRules(): array
    {
        return [
            'newPassword' => ['required', 'string', 'min:8', 'same:newPassword_confirmation'],
            'newPassword_confirmation' => ['required', 'string', 'min:8'],
        ];
    }

    public function savePassword(ChangeUserPassword $changeUserPassword): void
    {
        $this->authorize('user.update');
        $this->validate($this->passwordRules());
        
        $changeUserPassword->execute($this->passwordUserId, $this->newPassword);

        $this->showPasswordModal = false;
        $this->passwordUserId = null;
        $this->newPassword = '';
        $this->newPassword_confirmation = '';

        $this->dispatch('toast', message: 'User password updated successfully.', type: 'success');
    }

    #[Computed]
    public function users()
    {
        $filter = new UserFilter(
            search: $this->search !== '' ? $this->search : null, 
            onlyActive: $this->onlyActive ? true : null, 
            perPage: $this->perPage
        );

        return app(ListUsersWithEmployees::class)->execute($filter);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = collect($this->users->items())->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function bulkSuspend(ToggleUserStatus $toggleUserStatus): void
    {
        $this->authorize('user.update');
        
        if (empty($this->selectedRows)) return;

        foreach ($this->selectedRows as $userId) {
            $toggleUserStatus->execute((int) $userId);
        }

        $this->selectedRows = [];
        $this->selectAll = false;
        
        $this->dispatch('toast', message: 'Selected users have been toggled.', type: 'success');
    }

    public function openBulkRoleModal(): void
    {
        $this->authorize('user.update');
        if (empty($this->selectedRows)) return;
        
        $this->bulkRoleToAssign = '';
        $this->showBulkRoleModal = true;
    }

    public function executeBulkRole(): void
    {
        $this->authorize('user.update');
        $this->validate(['bulkRoleToAssign' => 'required|string|exists:roles,name']);

        if (empty($this->selectedRows)) return;

        $users = \App\Infrastructure\Persistence\Eloquent\Models\User::whereIn('id', $this->selectedRows)->get();
        foreach ($users as $user) {
            $user->assignRole($this->bulkRoleToAssign);
        }

        $this->showBulkRoleModal = false;
        $this->selectedRows = [];
        $this->selectAll = false;
        $this->bulkRoleToAssign = '';

        $this->dispatch('toast', message: 'Role assigned to selected users.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.users.user-index', [
            'users' => $this->users,
        ]);
    }
}
