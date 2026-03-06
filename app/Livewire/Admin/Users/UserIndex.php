<?php

namespace App\Livewire\Admin\Users;

use App\Application\Employee\UseCases\SearchEmployees;
use App\Application\User\DTOs\UserData;
use App\Application\User\DTOs\UserFilter;
use App\Application\User\UseCases\ChangeUserPassword;
use App\Application\User\UseCases\CreateUser;
use App\Application\User\UseCases\ListUsersWithEmployees;
use App\Application\User\UseCases\ToggleUserStatus;
use App\Application\User\UseCases\UpdateUser;
use App\Domain\Employee\Repositories\EmployeeRepository;
use App\Domain\User\Repositories\UserRepository;
use App\Infrastructure\Persistence\Eloquent\Models\User as EloquentUser;
use App\Presentation\Http\Requests\UserRequest;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserIndex extends Component
{
    use WithPagination;

    // Filters
    public string $search = '';

    public bool $onlyActive = false;

    public int $perPage = 10;

    // Modal state
    public bool $showModal = false;

    public ?int $editingId = null; // null = create, not null = edit

    // Form fields
    public string $name = '';

    public string $email = '';

    /** @var string[] */
    public array $selectedRoles = [];

    public bool $active = true;

    public string $password = '';

    public string $password_confirmation = '';

    /** @var string[] */
    public array $availableRoles = [];

    public bool $showPasswordModal = false;

    public ?int $passwordUserId = null;

    public string $newPassword = '';

    public string $newPassword_confirmation = '';

    // selectedEmployeeId
    public ?int $employeeId = null;

    public string $employeeSearch = '';

    public array $employeeOptions = [];

    public ?string $selectedEmployeeLabel = null;

    // Direct per-user permissions (fine-grained overrides on top of roles)
    /** @var string[] */
    public array $selectedDirectPermissions = [];

    // Modal tab state: 'roles' | 'permissions'
    public string $modalTab = 'roles';

    /** Human-readable descriptions for each role (used as tooltips in the UI). */
    protected array $roleDescriptions = [];

    public function mount(): void
    {
        $this->availableRoles    = Role::query()->orderBy('name')->pluck('name')->toArray();
        $this->roleDescriptions  = config('permission_groups.role_descriptions', []);
    }

    // Reset page when search/filter changes
    public function updatedSearch(): void    { $this->resetPage(); }
    public function updatedOnlyActive(): void { $this->resetPage(); }
    public function updatedPerPage(): void   { $this->resetPage(); }

    /** Grouped permissions for the Direct Permissions tab in the user modal. */
    public function getGroupedPermissionsProperty(): array
    {
        $all    = Permission::orderBy('name')->get();
        $groups = [];
        $used   = [];

        foreach (config('permission_groups.groups', []) as $label => $prefixes) {
            $prefixes = (array) $prefixes;
            $matched  = $all->filter(fn ($p) =>
                collect($prefixes)->contains(fn ($px) => str_starts_with($p->name, $px))
            );
            if ($matched->isNotEmpty()) {
                $groups[$label] = $matched->values();
                $used           = array_merge($used, $matched->pluck('name')->toArray());
            }
        }

        $other = $all->whereNotIn('name', $used);
        if ($other->isNotEmpty()) {
            $groups['Other'] = $other->values();
        }

        return $groups;
    }

    /** Returns the role description tooltip string. */
    public function getRoleDescription(string $role): string
    {
        return $this->roleDescriptions[$role] ?? ucfirst(str_replace('-', ' ', $role));
    }

    protected function rules(): array
    {
        // On create, password required; on edit, we ignore password here
        if (is_null($this->editingId)) {
            return UserRequest::storeRules();
        }

        return UserRequest::updateRules($this->editingId);
    }

    protected function messages(): array
    {
        return UserRequest::messagesArray();
    }

    protected function passwordRules(): array
    {
        return [
            'newPassword' => ['required', 'string', 'min:8', 'same:newPassword_confirmation'],
            'newPassword_confirmation' => ['required', 'string', 'min:8'],
        ];
    }

    public function toggleStatus(int $userId, ToggleUserStatus $toggleUserStatus): void
    {
        $this->authorize('user.update');

        $toggleUserStatus->execute($userId);
        $this->dispatch('flash', type: 'success', message: 'User status updated.');
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'email', 'selectedRoles', 'active', 'password', 'password_confirmation',
            'employeeId', 'employeeSearch', 'employeeOptions', 'selectedEmployeeLabel',
            'selectedDirectPermissions', 'modalTab']);

        $this->active    = true;
        $this->selectedRoles = [];
        $this->modalTab  = 'roles';
    }

    public function updatedEmployeeSearch(SearchEmployees $searchEmployees): void
    {
        $term = trim($this->employeeSearch);

        if ($term === '') {
            $this->employeeOptions = [];

            return;
        }

        $summaries = $searchEmployees->execute($term);
        $this->employeeOptions = array_map(function ($summary) {
            return [
                'id' => $summary->id,
                'nik' => $summary->nik,
                'name' => $summary->name,
                'branch' => $summary->branch,
                'dept_code' => $summary->deptCode,
            ];
        }, $summaries);
    }

    public function selectEmployee(int $employeeId): void
    {
        $option = collect($this->employeeOptions)->firstWhere('id', $employeeId);

        if (! $option) {
            // Nothing to select (optional: you could resolve EmployeeRepository here to re-fetch)
            return;
        }

        $this->employeeId = $option['id'];
        $this->selectedEmployeeLabel = sprintf('%s - %s (%s)', $option['nik'], $option['name'], $option['branch'] ?? '-');

        $this->employeeSearch = $option['nik'] . ' - ' . $option['name'];
        $this->employeeOptions = [];
    }

    public function openCreateModal(): void
    {
        $this->authorize('user.create');

        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $userId, UserRepository $users, EmployeeRepository $employees): void
    {
        $this->authorize('user.update');

        $this->resetForm();

        $user = $users->findById($userId);

        if (! $user) {
            $this->dispatch('flash', type: 'error', message: 'User not found.');

            return;
        }

        $this->editingId = $user->id();
        $this->name = $user->name();
        $this->email = (string) $user->email();
        $this->active = $user->isActive();
        $this->selectedRoles = $user->roles();
        $this->employeeId = method_exists($user, 'employeeId') ? $user->employeeId() : null;

        // Load direct (non-role) permissions for the user
        $eloquent = EloquentUser::find($user->id());
        $this->selectedDirectPermissions = $eloquent
            ? $eloquent->getDirectPermissions()->pluck('name')->toArray()
            : [];

        $this->modalTab = 'roles';

        if ($this->employeeId) {
            $employee = $employees->findById($this->employeeId);

            if ($employee) {
                $this->selectedEmployeeLabel = sprintf('%s - %s (%s)', $employee->nik(), $employee->name(), $employee->branch());
                $this->employeeSearch = $employee->nik() . ' - ' . $employee->name();
            }
        }

        $this->showModal = true;
    }

    public function openPasswordModal(int $userId): void
    {
        $this->authorize('user.update');

        $this->passwordUserId = $userId;
        $this->newPassword = '';
        $this->newPassword_confirmation = '';
        $this->showPasswordModal = true;
    }

    public function updatedShowModal($value)
    {
        if (! $value) {
            $this->reset('name', 'email', 'password', 'password_confirmation', 'employeeId', 'active',
                'selectedRoles', 'employeeSearch', 'employeeOptions', 'selectedEmployeeLabel',
                'selectedDirectPermissions', 'modalTab');
            $this->resetValidation();
        }

    }

    public function updatedShowPasswordModal($value)
    {
        if (! $value) {
            $this->reset('newPassword', 'newPassword_confirmation');
            $this->resetValidation();
        }
    }

    public function save(CreateUser $createUser, UpdateUser $updateUser): void
    {
        $this->validate();

        if (is_null($this->editingId)) {
            $this->authorize('user.create');
        } else {
            $this->authorize('user.update');
        }

        $password = is_null($this->editingId) ? $this->password : null;

        $dto = new UserData(name: $this->name, email: $this->email, password: $password, roles: $this->selectedRoles, active: $this->active, employeeId: $this->employeeId);

        if (is_null($this->editingId)) {
            $createUser->execute($dto);
            $this->dispatch('flash', type: 'success', message: 'User created successfully.');
        } else {
            $updateUser->execute($this->editingId, $dto);

            // Sync direct permissions (overrides on top of role permissions)
            $eloquent = EloquentUser::find($this->editingId);
            if ($eloquent) {
                $eloquent->syncPermissions($this->selectedDirectPermissions);
            }

            $this->dispatch('flash', type: 'success', message: 'User updated successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function savePassword(ChangeUserPassword $changeUserPassword): void
    {
        $this->authorize('user.update');
        
        $this->validate($this->passwordRules());
        if (! $this->passwordUserId) {
            $this->dispatch('flash', type: 'error', message: 'User not found.');
        }

        $changeUserPassword->execute($this->passwordUserId, $this->newPassword);
        $this->dispatch('flash', type: 'success', message: 'User password updated successfully.');

        $this->showPasswordModal = false;
        $this->passwordUserId = null;
        $this->newPassword = '';
        $this->newPassword_confirmation = '';
    }

    public function render(ListUsersWithEmployees $listUsers)
    {
        $filter = new UserFilter(search: $this->search !== '' ? $this->search : null, onlyActive: $this->onlyActive ? true : null, perPage: $this->perPage);

        $users = $listUsers->execute($filter);

        return view('livewire.admin.users.user-index', [
            'users'              => $users,
            'groupedPermissions' => $this->groupedPermissions,
        ])->layout('new.layouts.app');
    }
}
