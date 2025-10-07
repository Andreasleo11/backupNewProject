<?php

namespace App\Livewire\Compliance;

use App\Exports\Compliance\DashboardExport;
use App\Models\Department;
use App\Models\DepartmentComplianceMonthly;
use App\Models\DepartmentComplianceSnapshot;
use App\Models\RequirementUpload;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public string $search = '';

    public string $bucket = '';      // '', '0-49','50-99','100'

    public bool $hideComplete = false;

    public array $sparklines = []; // [deptId => [p1,p2,...]]

    protected function loadSparklinesFor(array $deptIds, int $months = 6): void
    {
        if (empty($deptIds)) {
            $this->sparklines = [];

            return;
        }

        $start = now()->startOfMonth()->subMonths($months - 1)->toDateString();

        $rows = DepartmentComplianceMonthly::query()
            ->whereIn('department_id', $deptIds)
            ->where('month', '>=', $start)
            ->orderBy('department_id')
            ->orderBy('month')
            ->get(['department_id', 'month', 'percent'])
            ->groupBy('department_id');

        $labels = collect(range(0, $months - 1))
            ->map(fn ($i) => now()->startOfMonth()->subMonths($months - 1 - $i)->toDateString())
            ->values();

        $out = [];
        foreach ($deptIds as $id) {
            $series = array_fill(0, $months, 0);
            foreach (($rows[$id] ?? collect()) as $row) {
                $idx = $labels->search($row->month);
                if ($idx !== false) {
                    $series[$idx] = (int) $row->percent;
                }
            }
            $out[$id] = $series;
        }
        $this->sparklines = $out;
    }

    public function mount()
    {
        $this->search = session('dashboard.search', '');
        $this->bucket = session('dashboard.bucket', '');
        $this->hideComplete = session('dashboard.hideComplete', false);
    }

    public function updated($field)
    {
        session()->put("dashboard.$field", $this->$field);
    }

    public function exportCsv() // keep name, but will download .xlsx
    {
        $snap = $this->filteredSnapshots()->with('department')->get();

        return Excel::download(new DashboardExport($snap), 'compliance-dashboard.xlsx');
    }

    private function filteredSnapshots()
    {
        return DepartmentComplianceSnapshot::query()
            ->when($this->search !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->whereHas('department', fn ($qq) => $qq
                    ->where('name', 'like', $term)->orWhere('code', 'like', $term));
            })
            ->when($this->bucket !== '', function ($q) {
                [$lo, $hi] = match ($this->bucket) {
                    '0-49' => [0, 49],
                    '50-99' => [50, 99],
                    '100' => [100, 100],
                    default => [0, 100],
                };
                $q->whereBetween('percent', [$lo, $hi]);
            })
            ->when($this->hideComplete, fn ($q) => $q->where('percent', '<', 100));
    }

    public function render()
    {
        // KPIs + distribution on filtered set
        $snap = $this->filteredSnapshots()->with('department')->get();

        $count = $snap->count();
        $avg = $count ? (int) round($snap->avg('percent')) : 0;
        $complete = $snap->where('percent', 100)->count();
        $below50 = $snap->whereBetween('percent', [0, 49])->count();
        $half99 = $snap->whereBetween('percent', [50, 99])->count();

        $total = max(1, $count);
        $dist = [
            'total' => $count,
            'c0_49' => $below50,
            'c50_99' => $half99,
            'c100' => $complete,
            'p0_49' => (int) round($below50 / $total * 100),
            'p50_99' => (int) round($half99 / $total * 100),
            'p100' => (int) round($complete / $total * 100),
        ];

        // Last updated (max snapshot time)
        $lastUpdated = $snap->max('generated_at');

        // Trend last 12 months (avg percent overall)
        $start = Carbon::now()->startOfMonth()->subMonths(11);
        $trendRaw = DepartmentComplianceMonthly::query()
            ->where('month', '>=', $start->toDateString())
            ->get()
            ->groupBy('month')
            ->map(fn ($g) => (int) round($g->avg('percent')))
            ->toArray();

        $labels = [];
        $values = [];
        $cursor = $start->copy();
        for ($i = 0; $i < 12; $i++) {
            $m = $cursor->toDateString();
            $labels[] = $cursor->format('M Y');
            $values[] = $trendRaw[$m] ?? 0;
            $cursor->addMonth();
        }

        // Bottom/Top 10 (global – not filtered, but you can apply filters if desired)
        $bottom = DepartmentComplianceSnapshot::with('department')
            ->orderBy('percent')->take(10)->get();

        $top = DepartmentComplianceSnapshot::with('department')
            ->orderByDesc('percent')->take(10)->get();

        // load last 6 months sparkline points for these departments
        $this->loadSparklinesFor(
            $bottom->pluck('department_id')->merge($top->pluck('department_id'))->unique()->all(),
            months: 6
        );

        // Pending approvals (latest 10)
        $pending = RequirementUpload::query()
            ->select([
                'requirement_uploads.*',
                'departments.name as dept_name',
                'requirements.name as req_name',
                'requirements.code as req_code',
            ])
            ->join('requirements', 'requirements.id', '=', 'requirement_uploads.requirement_id')
            ->join('departments', 'departments.id', '=', 'requirement_uploads.scope_id')
            ->where('scope_type', Department::class)
            ->where('status', 'pending')
            ->latest()->take(10)->get();

        // Expiring within 30 days (approved)
        $expiring = RequirementUpload::query()
            ->select([
                'requirement_uploads.*',
                'departments.name as dept_name',
                'requirements.name as req_name',
                'requirements.code as req_code',
            ])
            ->join('requirements', 'requirements.id', '=', 'requirement_uploads.requirement_id')
            ->join('departments', 'departments.id', '=', 'requirement_uploads.scope_id')
            ->where('scope_type', Department::class)
            ->where('status', 'approved')
            ->whereNotNull('valid_until')
            ->whereBetween('valid_until', [now(), now()->addDays(30)])
            ->orderBy('valid_until')
            ->take(10)->get();

        return view('livewire.compliance.dashboard', [
            'kpi' => compact('count', 'avg', 'complete', 'half99', 'below50'),
            'trendLabels' => $labels,
            'trendValues' => $values,
            'bottom' => $bottom,
            'top' => $top,
            'pending' => $pending,
            'expiring' => $expiring,
            'dist' => $dist,
            'bucket' => $this->bucket,
            'lastUpdated' => $lastUpdated ? Carbon::parse($lastUpdated) : null,
        ]);
    }
}
