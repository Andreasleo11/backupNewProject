<?php

namespace App\Livewire\Departments;

use App\Models\Department;
use App\Services\ComplianceService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('new.layouts.app')]
class Overview extends Component
{
    use WithPagination;

    public $paginationTheme = 'bootstrap';

    public string $search = '';

    public string $status = 'all';          // all|complete|incomplete

    public string $bucket = '';             // '', '0-49','50-99','100'

    public string $sort = 'name';           // name|code|percent

    public string $dir = 'asc';            // asc|desc

    public int $perPage = 10;

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => 'all'],
        'bucket' => ['except' => ''],
        'sort' => ['except' => 'name'],
        'dir' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    public function updated($field)
    {
        if (in_array($field, ['search', 'status', 'bucket', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['name', 'code', 'percent'])) {
            return;
        }
        if ($this->sort === $field) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $field;
            $this->dir = 'asc';
        }
        $this->resetPage();
    }

    public function sortIcon(string $field): string
    {
        if ($this->sort !== $field) {
            // Not active sort column → neutral icon
            return '<i class="bi bi-arrow-down-up text-muted small ms-1"></i>';
        }

        // Active sort column → show asc/desc arrow
        return $this->dir === 'asc'
            ? '<i class="bi bi-arrow-up text-primary small ms-1"></i>'
            : '<i class="bi bi-arrow-down text-primary small ms-1"></i>';
    }

    public function toggleDir(): void
    {
        $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
    }

    public function render(ComplianceService $svc)
    {
        // Base list (DB filter only for search)
        $page = Department::query()
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term);
                });
            })
            ->orderBy('name') // preliminary order; we’ll re-sort after computing percent if needed
            ->paginate($this->perPage);

        // Compute percent on current page
        $rows = $page->getCollection()->map(function (Department $d) use ($svc) {
            $percent = (int) round($svc->getScopeCompliancePercent($d));

            return [
                'dept' => $d,
                'percent' => $percent,
                'status' => $percent >= 100 ? 'Complete' : 'Incomplete',
            ];
        });

        // Status filter (view-level)
        if ($this->status !== 'all') {
            $want = $this->status === 'complete' ? 'Complete' : 'Incomplete';
            $rows = $rows->filter(fn ($r) => $r['status'] === $want);
        }

        // Bucket filter
        if ($this->bucket !== '') {
            [$lo,$hi] = match ($this->bucket) {
                '0-49' => [0, 49],
                '50-99' => [50, 99],
                '100' => [100, 100],
                default => [0, 100],
            };
            $rows = $rows->filter(fn ($r) => $r['percent'] >= $lo && $r['percent'] <= $hi);
        }

        // Sorting (client-side) on the current page
        $rows = $rows->sortBy(function ($r) {
            return match ($this->sort) {
                'code' => $r['dept']->code ?? '',
                'percent' => $r['percent'],
                default => $r['dept']->name,
            };
        }, SORT_REGULAR, $this->dir === 'desc')->values();

        // KPIs for current page after filters
        $count = $rows->count();
        $complete = $rows->where('percent', 100)->count();
        $incomplete = $count - $complete;
        $avg = $count ? (int) round($rows->avg('percent')) : 0;
        $kpi = compact('count', 'complete', 'incomplete', 'avg');

        // Swap paginator’s collection with our decorated, sorted rows
        $page->setCollection($rows);

        return view('livewire.departments.overview', [
            'items' => $page,
            'kpi' => $kpi,
        ]);
    }
}
