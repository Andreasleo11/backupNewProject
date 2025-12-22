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
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public ?string $branchFilter = null;
    public bool $onlyActive = false;
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
        'refreshDepartments' => '$refresh'
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
        if(is_null($this->editingId)) {
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
        $this->resetForm();
        $this->editingId = null;
        $this->showModal = true;
    }

    public function openEditModal(int $id, DepartmentRepository $departments): void
    {
        $this->resetForm();
        $department = $departments->findById($id);

        if(! $department) {
            session()->flash('error', 'Department not found');
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
        $dto = new DepartmentData(
            id: $this->editingId,
            deptNo: $this->dept_no,
            name: $this->name,
            code: $this->code,
            branch: $this->branch,
            isOffice: $this->is_office,
            isActive: $this->is_active,
        );

        if(is_null($this->editingId)) {
            $createDepartment->execute($dto);
            session()->flash('success', 'Department created successfully!');
        } else {
            $updateDepartment->execute($this->editingId, $dto);
            session()->flash('success', 'Department updated successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
    }

    public function toggleStatus(int $id, ToggleDepartmentStatus $toggleDepartmentStatus): void
    {
        $toggleDepartmentStatus->execute($id);
        session()->flash('success', 'Department status updated.');
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
