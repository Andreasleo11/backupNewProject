<?php

namespace App\Livewire\Departments;

use App\Models\Department;
use App\Services\ComplianceService;
use Livewire\Attributes\On;
use Livewire\Component;

class Compliance extends Component
{
    public Department $department;

    public array $rows = [];

    public int $percent = 0;

    private function loadCompliance(Department $department, ComplianceService $svc): void
    {
        $list = $svc->getScopeCompliance($department);

        $this->rows = $list->map(fn ($r) => [
            'id' => $r['requirement']->id,
            'code' => $r['requirement']->code,
            'name' => $r['requirement']->name,
            'status' => $r['status'],
            'valid_count' => $r['valid_count'],
            'min' => $r['requirement']->min_count,
            'requires_approval' => $r['requirement']->requires_approval,
        ])->toArray();

        $this->percent = $svc->getScopeCompliancePercent($department);
    }

    #[On('upload:done')]
    public function reload(ComplianceService $svc): void
    {
        $this->loadCompliance($this->department, $svc);
    }

    public function openUpload(int $requirementId): void
    {
        $this->dispatch('open-upload', requirementId: $requirementId);
    }

    public function mount(Department $department, ComplianceService $svc): void
    {
        $list = $svc->getScopeCompliance($department);
        $this->loadCompliance($department, $svc);

        $this->percent = $svc->getScopeCompliancePercent($department);
    }

    public function render()
    {
        return view('livewire.departments.compliance');
    }
}
