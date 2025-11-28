<?php

namespace App\Livewire\Requirements;

use App\Models\Department;
use App\Models\Requirement;
use App\Models\RequirementUpload;
use Livewire\Attributes\On;
use Livewire\Component;

class RecentUploads extends Component
{
    public ?Department $department = null;
    public ?Requirement $requirement = null;

    /** @var \Illuminate\Support\Collection */
    public $uploads;

    #[On('open-recent-uploads')]
    public function open(int $requirementId, int $departmentId): void
    {
        $this->department = Department::findOrFail($departmentId);
        $this->requirement = Requirement::findOrFail($requirementId);
        $this->load();
        $this->dispatch('show-recent-uploads');
    }

    public function load(): void
    {
       $this->uploads = RequirementUpload::query()
        ->with('uploadedBy')
        ->where('requirement_id', $this->requirement->id)
        ->where('scope_type', Department::class)
        ->where('scope_id', $this->department->id)
        ->latest()
        ->take(20)
        ->get();
    }

    public function render()
    {
        return view('livewire.requirements.recent-uploads');
    }
}
