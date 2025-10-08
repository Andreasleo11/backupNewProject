<?php

namespace App\Livewire\Requirements;

use App\Models\Department;
use App\Models\Requirement;
use App\Models\RequirementAssignment;
use App\Models\RequirementUpload;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Departments extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';

    public Requirement $requirement;

    public string $search = '';

    public string $status = 'all'; // all|ok|missing|pending

    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'perPage' => ['except' => 10],
    ];

    public function mount(Requirement $requirement): void
    {
        $this->requirement = $requirement;
    }

    public function updated($field)
    {
        if (in_array($field, ['search', 'status', 'perPage'])) {
            $this->resetPage();
        }
    }

    /** compute valid uploads for dept against this requirement */
    private function validCountFor(Department $dept): int
    {
        $today = Carbon::today();

        $q = RequirementUpload::query()
            ->where('requirement_id', $this->requirement->id)
            ->where('scope_type', Department::class)
            ->where('scope_id', $dept->id);

        // Approval gate
        if ($this->requirement->requires_approval) {
            $q->where('status', 'approved');
        } else {
            // uploads are saved as approved already in your flow, so this is effectively no-op
            $q->whereIn('status', ['approved', 'pending']);
        }

        // Valid date window
        $q->where(function ($qq) use ($today) {
            $qq->whereNull('valid_until')->orWhere('valid_until', '>=', $today);
        });

        return $q->count();
    }

    public function render()
    {
        $assignedDeptIds = RequirementAssignment::query()
            ->where('requirement_id', $this->requirement->id)
            ->where('scope_type', Department::class)
            ->pluck('scope_id');

        $departments = Department::query()
            ->whereIn('id', $assignedDeptIds)
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        $rows = $departments->getCollection()->map(function (Department $d) {
            $valid = $this->validCountFor($d);
            $min = $this->requirement->min_count;
            $status = $valid >= $min ? 'OK' : 'Missing';

            $pending = 0;
            if ($this->requirement->requires_approval) {
                $pending = RequirementUpload::where([
                    'requirement_id' => $this->requirement->id,
                    'scope_type' => Department::class,
                    'scope_id' => $d->id,
                    'status' => 'pending',
                ])->count();
                if ($status === 'Missing' && $pending > 0) {
                    $status = 'Pending';
                }
            }

            return compact('d', 'valid', 'min', 'status', 'pending');
        })->map(function ($r) { // rename keys for blade
            return [
                'dept' => $r['d'],
                'valid' => $r['valid'],
                'min' => $r['min'],
                'status' => $r['status'],
                'pending' => $r['pending'],
            ];
        });

        // Status filter (after computing)
        if ($this->status !== 'all') {
            $rows = $rows->filter(fn ($r) => strtolower($r['status']) === $this->status);
        }

        // Summary counts before slicing into paginator collection
        $summary = [
            'total' => $rows->count(),                 // assigned and visible after filter
            'ok' => $rows->where('status', 'OK')->count(),
            'pending' => $rows->where('status', 'Pending')->count(),
            'missing' => $rows->where('status', 'Missing')->count(),
        ];

        // Swap paginator’s collection
        $departments->setCollection($rows->values());

        return view('livewire.requirements.departments', [
            'items' => $departments,
            'req' => $this->requirement,
            'summary' => $summary,
        ]);
    }
}
