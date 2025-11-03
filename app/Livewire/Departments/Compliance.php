<?php

namespace App\Livewire\Departments;

use App\Models\Department;
use App\Models\RequirementUpload;
use App\Services\ComplianceService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class Compliance extends Component
{
    public Department $department;

    public array $rows = [];

    public int $percent = 0;

    public string $search = '';

    public string $status = 'all'; // all|ok|missing|pending

    public string $sort = 'code'; // code|name|percent|expires

    public string $dir = 'asc'; // asc|desc

    public bool $onlyUnmet = false; // quick toggle

    private function loadCompliance(Department $department, ComplianceService $svc): void
    {
        $list = $svc->getScopeCompliance($department);

        $this->rows = $list->map(function ($r) use ($department) {
            $req = $r['requirement'];
            $validCount = (int) $r['valid_count'];
            $min = (int) $req->min_count;

            // Pending count (only meaningful if requires approval)
            $pending = 0;
            if ($req->requires_approval) {
                $pending = RequirementUpload::where([
                    'requirement_id' => $req->id,
                    'scope_type' => Department::class,
                    'scope_id' => $department->id,
                    'status' => 'pending',
                ])->count();
            }

            // Expiry window (latest valid_until among approved/current files)
            $today = Carbon::today();
            $latest = RequirementUpload::query()
                ->where([
                    'requirement_id' => $req->id,
                    'scope_type' => Department::class,
                    'scope_id' => $department->id,
                ])
                ->when($req->requires_approval, fn ($q) => $q->where('status', 'approved'))
                ->where(function ($q) use ($today) {
                    $q->whereNull('valid_until')->orWhere('valid_until', '>=', $today);
                })
                ->max('valid_until'); // null if none with expiry

            $lastValidUntil = $latest ? Carbon::parse($latest) : null;

            // Heuristic “next due” based on frequency & validity_days
            $nextDue = null;
            if ($req->frequency !== 'once') {
                if ($lastValidUntil) {
                    // When it expires, the next due is that date (or next cycle)
                    $nextDue = $lastValidUntil;
                } elseif ($validCount < $min) {
                    // If missing, it’s effectively “now”
                    $nextDue = $today;
                }
            }

            // Allowed types (for tooltip)
            $allowed = $req->allowed_mimetypes ?? [];
            $allowedSummary = empty($allowed)
                ? 'Any file'
                : implode(', ', array_map(fn ($m) => Str::upper($m), $allowed));

            // Status + percent
            $status = $validCount >= $min ? 'OK' : 'Missing';
            if ($status === 'Missing' && $pending > 0) {
                $status = 'Pending';
            }
            $percent = (int) round(min(100, ($validCount / max(1, $min)) * 100));

            return [
                'id' => $req->id,
                'code' => $req->code,
                'name' => $req->name,
                'status' => $status,           // OK|Missing|Pending
                'valid_count' => $validCount,
                'min' => $min,
                'requires_approval' => (bool) $req->requires_approval,
                'pending' => $pending,
                'percent' => $percent,
                'last_valid_until' => $lastValidUntil,   // Carbon|null
                'next_due' => $nextDue,          // Carbon|null
                'allowed_summary' => $allowedSummary,
            ];
        })->values()->toArray();

        $this->percent = $svc->getScopeCompliancePercent($department);
    }

    #[On('upload:done')]
    public function reload(ComplianceService $svc): void
    {
        $this->loadCompliance($this->department, $svc);
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['code', 'name', 'percent', 'expires'], true)) {
            return;
        }
        $this->dir = $this->sort === $field && $this->dir === 'asc' ? 'desc' : 'asc';
        $this->sort = $field;
    }

    public function openUpload(int $requirementId): void
    {
        $this->dispatch('open-upload', requirementId: $requirementId, departmentId: $this->department->id);
    }

    public function mount(Department $department, ComplianceService $svc): void
    {
        $this->department = $department;
        $this->loadCompliance($department, $svc);
    }

    public function getFilteredSortedRowsProperty(): array
    {
        $rows = collect($this->rows);

        // Filters
        if ($this->search !== '') {
            $term = mb_strtolower($this->search);
            $rows = $rows->filter(fn ($r) =>
                str_contains(mb_strtolower($r['code']), $term) ||
                str_contains(mb_strtolower($r['name']), $term)
            );
        }
        if ($this->status !== 'all') {
            $rows = $rows->filter(fn ($r) => strtolower($r['status']) === $this->status);
        }
        if ($this->onlyUnmet) {
            $rows = $rows->filter(fn ($r) => $r['valid_count'] < $r['min']);
        }

        // Sort
        $rows = $rows->sortBy(function ($r) {
            return match ($this->sort) {
                'name'    => $r['name'],
                'percent' => $r['percent'],
                'expires' => $r['last_valid_until']?->timestamp ?? -INF,
                default   => $r['code'],
            };
        }, SORT_REGULAR, $this->dir === 'desc');

        return $rows->values()->all();
    }

    public function render()
    {
        return view('livewire.departments.compliance');
    }
}
