<?php

namespace App\Livewire\Requirements;

use App\Models\Department;
use App\Models\Requirement;
use App\Models\RequirementAssignment;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Assign extends Component
{
    #[Validate('required|exists:requirements,id')]
    public $requirement_id;

    #[Validate('array|min:1')]
    public $department_ids = [];

    public bool $is_mandatory = true;

    public function save()
    {
        $this->validate();
        $req = Requirement::findOrFail($this->requirement_id);
        foreach ($this->department_ids as $deptId) {
            RequirementAssignment::updateOrCreate(
                [
                    'requirement_id' => $req->id,
                    'scope_type' => Department::class,
                    'scope_id' => $deptId,
                ],
                ['is_mandatory' => $this->is_mandatory]
            );
        }
        $this->dispatch('toast', type: 'success', message: 'Assigned successfully');
    }

    public function render()
    {
        return view('livewire.requirements.assign', [
            'requirements' => Requirement::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }
}
