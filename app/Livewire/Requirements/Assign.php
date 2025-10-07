<?php

namespace App\Livewire\Requirements;

use App\Models\Department;
use App\Models\Requirement;
use App\Models\RequirementAssignment;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Assign extends Component
{
    #[Validate('required|exists:requirements,id')]
    public $requirement_id = '';

    #[Validate('array|min:1')]
    public $department_ids = [];

    public bool $is_mandatory = true;

    // UI helpers
    public string $deptSearch = '';

    // Derived (computed in render)
    public array $assignedDeptIds = []; // for selected requirement

    public int $willCreate = 0;

    public int $willUpdate = 0;

    public function updatedRequirementId(): void
    {
        // Reset selection when switching requirement to avoid accidental bulk update
        $this->department_ids = [];
        $this->syncAssignedIds();
    }

    public function selectAll(): void
    {
        $this->department_ids = Department::pluck('id')->all();
        $this->recountPreview();
    }

    public function selectNone(): void
    {
        $this->department_ids = [];
        $this->recountPreview();
    }

    public function selectAssigned(): void
    {
        $this->department_ids = $this->assignedDeptIds;
        $this->recountPreview();
    }

    public function selectUnassigned(): void
    {
        $all = Department::pluck('id')->all();
        $this->department_ids = array_values(array_diff($all, $this->assignedDeptIds));
        $this->recountPreview();
    }

    public function updatedDeptSearch(): void
    {
        // no-op; just re-render with filtered list
    }

    public function updatedDepartmentIds(): void
    {
        $this->recountPreview();
    }

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

        $this->syncAssignedIds();
        $this->recountPreview();

        $this->dispatch('toast', type: 'success', message: 'Assigned successfully');
    }

    public function unassign()
    {
        $this->validate([
            'requirement_id' => 'required|exists:requirements,id',
            'department_ids' => 'array|min:1',
        ]);

        RequirementAssignment::query()
            ->where('requirement_id', $this->requirement_id)
            ->where('scope_type', Department::class)
            ->whereIn('scope_id', $this->department_ids)
            ->delete();

        $this->dispatch('toast', type: 'info', message: 'Unassigned successfully.');

        // Refresh assigned list & preview
        $this->syncAssignedIds();
        $this->recountPreview();

        // Optionally clear selection
        $this->department_ids = [];
    }

    private function syncAssignedIds(): void
    {
        if (! $this->requirement_id) {
            $this->assignedDeptIds = [];

            return;
        }

        $this->assignedDeptIds = RequirementAssignment::query()
            ->where('requirement_id', $this->requirement_id)
            ->where('scope_type', Department::class)
            ->pluck('scope_id')
            ->all();
    }

    private function recountPreview(): void
    {
        $sel = collect($this->department_ids)->map(fn ($v) => (int) $v)->unique();

        $assigned = collect($this->assignedDeptIds);
        $this->willCreate = $sel->diff($assigned)->count();   // new pairs
        $this->willUpdate = $sel->intersect($assigned)->count(); // may update is_mandatory
    }

    public function mount(): void
    {
        $this->syncAssignedIds();
        $this->recountPreview();
    }

    public function render()
    {
        // Data sources
        $requirements = Requirement::orderBy('name')->get();

        /** @var Collection<int,Department> $departments */
        $departments = Department::query()
            ->when($this->deptSearch !== '', function ($q) {
                $term = '%'.$this->deptSearch.'%';
                $q->where(fn ($qq) => $qq->where('name', 'like', $term)->orWhere('code', 'like', $term));
            })
            ->orderBy('name')
            ->get();

        // Selected requirement (for facts panel)
        $req = $this->requirement_id ? $requirements->firstWhere('id', (int) $this->requirement_id) : null;

        // recent activity (last 8) for the chosen requirement
        $recent = collect();
        if ($req) {
            $recent = RequirementAssignment::query()
                ->with('scope') // scope is Department
                ->where('requirement_id', $req->id)
                ->where('scope_type', Department::class)
                ->latest()
                ->take(8)
                ->get();
        }

        // keep preview in sync if requirement changed externally
        if ($req && empty($this->assignedDeptIds)) {
            $this->syncAssignedIds();
            $this->recountPreview();
        }

        return view('livewire.requirements.assign', compact('requirements', 'departments', 'req', 'recent'));
    }
}
