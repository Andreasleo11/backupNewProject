<?php

namespace App\Livewire\Admin\Employees;

use App\Application\Employee\DTOs\EmployeeFilter;
use App\Application\Employee\UseCases\ListEmployees;
use Livewire\Component;
use Livewire\WithPagination;

class EmployeeIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 10;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(ListEmployees $listEmployees)
    {
        $filter = new EmployeeFilter(
            search: $this->search,
            perPage: $this->perPage,
        );

        $employees = $listEmployees->execute($filter);
        return view('livewire.admin.employees.employee-index', ['employees' => $employees]);
    }
}
