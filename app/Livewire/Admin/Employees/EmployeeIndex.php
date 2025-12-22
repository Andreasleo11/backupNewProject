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

    public string $sortBy = 'nik';          // default sort column

    public string $sortDirection = 'asc';   // default sort direction

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sort_by($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function render(ListEmployees $listEmployees)
    {
        $filter = new EmployeeFilter(
            search: $this->search,
            perPage: $this->perPage,
            sortBy: $this->sortBy,
            sortDirection: $this->sortDirection,
        );

        $employees = $listEmployees->execute($filter);

        return view('livewire.admin.employees.employee-index', ['employees' => $employees]);
    }
}
