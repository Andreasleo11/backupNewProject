<?php

namespace App\Livewire\Admin\Users;

use App\Application\Employee\UseCases\SearchEmployees;
use App\Application\User\DTOs\UserData;
use App\Application\User\UseCases\CreateUser;
use App\Infrastructure\Common\PermissionRegistry;
use App\Presentation\Http\Requests\UserRequest;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UserCreate extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $active = true;

    /** @var string[] */
    public array $selectedRoles = [];
    public array $availableRoles = [];
    
    public ?int $employeeId = null;
    public string $employeeSearch = '';
    public array $employeeOptions = [];
    public ?string $selectedEmployeeLabel = null;

    protected array $roleDescriptions = [];

    public function mount(): void
    {
        $this->availableRoles = Role::query()->orderBy('name')->pluck('name')->toArray();
        $this->roleDescriptions = config('permission_groups.role_descriptions', []);
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
        return UserRequest::storeRules();
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

    public function save(CreateUser $createUser): void
    {
        $this->authorize('user.create');
        $this->validate();

        $dto = new UserData(
            name: $this->name, 
            email: $this->email, 
            password: $this->password, 
            roles: $this->selectedRoles, 
            active: $this->active, 
            employeeId: $this->employeeId
        );

        $createUser->execute($dto);

        session()->flash('success', 'User created successfully.');
        $this->redirectRoute('admin.users.index');
    }

    public function render()
    {
        return view('livewire.admin.users.user-create');
    }
}
