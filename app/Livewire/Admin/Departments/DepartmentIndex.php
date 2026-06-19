<?php

namespace App\Livewire\Admin\Departments;

use App\Application\Department\DTOs\DepartmentData;
use App\Application\Department\DTOs\DepartmentFilter;
use App\Application\Department\UseCases\CreateDepartment;
use App\Application\Department\UseCases\ListDepartments;
use App\Application\Department\UseCases\ToggleDepartmentStatus;
use App\Application\Department\UseCases\UpdateDepartment;
use App\Domain\Department\Repositories\DepartmentRepository;
use App\Presentation\Http\Requests\DepartmentRequest;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentIndex extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public string $search = '';

    #[Url(history: true)]
    public ?string $branchFilter = null;

    #[Url(history: true)]
    public bool $onlyActive = false;

    #[Url(history: true)]
    public int $perPage = 10;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $dept_no = '';

    public ?string $branch = 'JAKARTA';

    public bool $is_active = true;

    public bool $is_office = true;

    protected $listeners = [
        'refreshDepartments' => '$refresh',
    ];

    public function updateSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBranchFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        if (is_null($this->editingId)) {
            return DepartmentRequest::storeRules();
        }

        return DepartmentRequest::updateRules($this->editingId);
    }

    protected function messages(): array
    {
        return DepartmentRequest::messagesArray();
    }

    public function openCreateModal(): void
    {
        $this->authorize('department.create');

        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $id, DepartmentRepository $departments): void
    {
        $this->authorize('department.update');

        $this->resetForm();
        $department = $departments->findById($id);

        if (! $department) {
            $this->dispatch('toast', message: 'Department not found.', type: 'error');

            return;
        }

        $this->editingId = $department->id();
        $this->dept_no = $department->deptNo();
        $this->name = $department->name();
        $this->code = $department->code();
        $this->branch = $department->branch();
        $this->is_active = $department->isActive();
        $this->is_office = $department->isOffice();

        $this->showModal = true;
    }

    public function save(CreateDepartment $createDepartment, UpdateDepartment $updateDepartment): void
    {
        $this->validate();

        if (is_null($this->editingId)) {
            $this->authorize('department.create');
        } else {
            $this->authorize('department.update');
        }

        $dto = new DepartmentData(
            id: $this->editingId,
            deptNo: $this->dept_no,
            name: $this->name,
            code: $this->code,
            branch: $this->branch,
            isOffice: $this->is_office,
            isActive: $this->is_active,
        );

        if (is_null($this->editingId)) {
            $createDepartment->execute($dto);
            $this->dispatch('toast', message: 'Department created successfully!', type: 'success');
        } else {
            $updateDepartment->execute($this->editingId, $dto);
            $this->dispatch('toast', message: 'Department updated successfully!', type: 'success');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function toggleStatus(int $id, ToggleDepartmentStatus $toggleDepartmentStatus): void
    {
        $this->authorize('department.update');

        $toggleDepartmentStatus->execute($id);
        $this->dispatch('toast', message: 'Department status updated.', type: 'success');
        $this->resetPage();
    }

    private function resetForm(): void
    {
        $this->reset([
            'code', 'dept_no', 'name', 'code', 'branch', 'is_active', 'is_office', 'is_active',
        ]);

        $this->branch = 'JAKARTA';
        $this->is_active = true;
        $this->is_office = true;
    }

    public function render(ListDepartments $listDepartments)
    {
        $filter = new DepartmentFilter(
            search: $this->search !== '' ? $this->search : null,
            branch: $this->branchFilter !== '' ? $this->branchFilter : null,
            onlyActive: $this->onlyActive ? true : false,
            perPage: $this->perPage,
        );

        $departments = $listDepartments->execute($filter);

        return view('livewire.admin.departments.department-index', ['departments' => $departments]);
    }
}
