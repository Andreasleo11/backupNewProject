<?php

namespace App\Livewire\Admin\Users;

use App\Application\User\DTOs\UserFilter;
use App\Application\User\UseCases\ChangeUserPassword;
use App\Application\User\UseCases\ListUsersWithEmployees;
use App\Application\User\UseCases\ToggleUserStatus;
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

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedOnlyActive(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
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

    public function updatedSelectAll($value)
    {
        if ($value) {
            $filter = new UserFilter(
                search: $this->search !== '' ? $this->search : null, 
                onlyActive: $this->onlyActive ? true : null, 
                perPage: $this->perPage
            );
            $users = app(ListUsersWithEmployees::class)->execute($filter);
            $this->selectedRows = $users->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    public function render(ListUsersWithEmployees $listUsers)
    {
        $filter = new UserFilter(
            search: $this->search !== '' ? $this->search : null, 
            onlyActive: $this->onlyActive ? true : null, 
            perPage: $this->perPage
        );

        $users = $listUsers->execute($filter);

        return view('livewire.admin.users.user-index', [
            'users' => $users,
        ]);
    }
}
