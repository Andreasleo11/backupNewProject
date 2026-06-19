<?php

namespace App\Livewire\Admin\Users;

use App\Application\Employee\UseCases\SearchEmployees;
use App\Application\User\DTOs\UserData;
use App\Application\User\UseCases\UpdateUser;
use App\Domain\Employee\Repositories\EmployeeRepository;
use App\Domain\User\Repositories\UserRepository;
use App\Infrastructure\Common\PermissionRegistry;
use App\Infrastructure\Persistence\Eloquent\Models\User as EloquentUser;
use App\Presentation\Http\Requests\UserRequest;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserEdit extends Component
{
    public int $editingId;
    public string $name = '';
    public string $email = '';
    public bool $active = true;

    /** @var string[] */
    public array $selectedRoles = [];
    public array $availableRoles = [];
    
    public array $originalDirectPermissions = [];
    public array $selectedDirectPermissions = [];
    
    public ?int $employeeId = null;
    public string $employeeSearch = '';
    public array $employeeOptions = [];
    public ?string $selectedEmployeeLabel = null;

    protected array $roleDescriptions = [];

    public function mount(int $userId, UserRepository $users, EmployeeRepository $employees): void
    {
        $this->availableRoles = Role::query()->orderBy('name')->pluck('name')->toArray();
        $this->roleDescriptions = config('permission_groups.role_descriptions', []);

        $user = $users->findById($userId);

        if (! $user) {
            abort(404, 'User not found.');
        }

        $this->editingId = $user->id();
        $this->name = $user->name();
        $this->email = (string) $user->email();
        $this->active = $user->isActive();
        $this->selectedRoles = $user->roles();
        $this->employeeId = method_exists($user, 'employeeId') ? $user->employeeId() : null;

        if ($this->employeeId) {
            $employee = $employees->findById($this->employeeId);
            if ($employee) {
                $this->selectedEmployeeLabel = sprintf('%s - %s (%s)', $employee->nik(), $employee->name(), $employee->branch());
                $this->employeeSearch = $employee->nik() . ' - ' . $employee->name();
            }
        }

        $eloquent = EloquentUser::find($this->editingId);
        $this->originalDirectPermissions = $eloquent
            ? $eloquent->getDirectPermissions()->pluck('name')->toArray()
            : [];
        $this->selectedDirectPermissions = $this->originalDirectPermissions;
    }

    public function getGroupedRolesProperty(): array
    {
        $allModules = PermissionRegistry::getModules();
        $grouped = [];
        $assignedRoles = [];

        foreach ($allModules as $moduleName => $data) {
            $moduleRoles = array_keys($data['roles'] ?? []);
            if (! empty($moduleRoles)) {
                $validRoles = array_intersect($moduleRoles, $this->availableRoles);
                if (! empty($validRoles)) {
                    $grouped[$moduleName] = $validRoles;
                    $assignedRoles = array_merge($assignedRoles, $validRoles);
                }
            }
        }

        $others = array_diff($this->availableRoles, $assignedRoles);
        if (! empty($others)) {
            $grouped['Other'] = array_values($others);
        }

        return $grouped;
    }

    public function getRoleDescription(string $role): string
    {
        return $this->roleDescriptions[$role] ?? ucfirst(str_replace('-', ' ', $role));
    }

    protected function rules(): array
    {
        return UserRequest::updateRules($this->editingId);
    }

    protected function messages(): array
    {
        return UserRequest::messagesArray();
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
        if (! $option) return;

        $this->employeeId = $option['id'];
        $this->selectedEmployeeLabel = sprintf('%s - %s (%s)', $option['nik'], $option['name'], $option['branch'] ?? '-');
        $this->employeeSearch = $option['nik'] . ' - ' . $option['name'];
        $this->employeeOptions = [];
    }

    public function save(UpdateUser $updateUser): void
    {
        $this->authorize('user.update');
        $this->validate();

        $dto = new UserData(
            name: $this->name, 
            email: $this->email, 
            password: null, 
            roles: $this->selectedRoles, 
            active: $this->active, 
            employeeId: $this->employeeId
        );

        $updateUser->execute($this->editingId, $dto);

        $eloquent = EloquentUser::find($this->editingId);
        if ($eloquent) {
            $eloquent->syncPermissions($this->selectedDirectPermissions);
        }

        session()->flash('success', 'User updated successfully.');
        $this->redirectRoute('admin.users.index');
    }

    public function render()
    {
        return view('livewire.admin.users.user-edit');
    }
}
