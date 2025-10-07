<?php

namespace App\Livewire\Departments;

use App\Models\Department;
use App\Services\ComplianceService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Overview extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = 'all'; // all|complete|incomplete

    public int $perPage = 10;

    public function render(ComplianceService $svc)
    {
        $page = Department::query()
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where('name', 'like', $term)->orWhere('code', 'like', $term);
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        $rows = $page->getCollection()->map(function (Department $d) use ($svc) {
            $percent = $svc->getScopeCompliancePercent($d); // you already have this

            return [
                'dept' => $d,
                'percent' => $percent,
                'status' => $percent >= 100 ? 'Complete' : 'Incomplete',
            ];
        });

        if ($this->status !== 'all') {
            $want = $this->status === 'complete' ? 'Complete' : 'Incomplete';
            $rows = $rows->filter(fn ($r) => $r['status'] === $want);
        }

        $page->setCollection($rows->values());

        return view('livewire.departments.overview', ['items' => $page]);
    }
}
