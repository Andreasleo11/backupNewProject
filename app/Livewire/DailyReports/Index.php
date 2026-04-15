<?php

namespace App\Livewire\DailyReports;

use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Infrastructure\Persistence\Eloquent\Models\EmployeeDailyReport;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected $listeners = ['refreshIndex' => '$refresh'];

    // Query-string synced filters
    #[Url(as: 'q')]
    public ?string $search = null;

    #[Url(as: 'filter_department_no')]
    public ?string $departmentNo = null;

    #[Url(as: 'filter_jabatan')]
    public ?string $jabatan = null;

    #[Url(as: 'from')]
    public ?string $dateFrom = null; // YYYY-MM-DD

    #[Url(as: 'to')]
    public ?string $dateTo = null; // YYYY-MM-DD

    public array $departmentNos = []; // [['dept_no'=>..., 'name'=>...], ...]

    public array $positions = []; // unique jabatan list

    /** @var array<string> */
    protected array $validNiks = [];

    public function mount()
    {
        $user = auth()->user();

        // Authorization (same logic as controller)
        if (! $user->is_head && ! $user->hasRole('DIRECTOR') && ! $user->hasRole('super-admin')) {
            abort(403, 'Anda tidak memiliki akses');
        }

        // Scope employees to department (unless Bernadett or DIRECTOR)
        $employeeQuery = Employee::query()->whereNull('end_date');
        if ($user->name !== 'Bernadett' && ! $user->hasRole('DIRECTOR') && ! $user->hasRole('super-admin')) {
            $employeeQuery->where('dept_code', $user->department?->dept_no);
        }

        $validEmployees = $employeeQuery->get(['nik', 'name', 'position', 'dept_code']);
        $this->validNiks = $validEmployees->pluck('nik')->all();

        $this->positions = $validEmployees
            ->pluck('position')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        $this->departmentNos = Department::orderBy('dept_no')
            ->get(['dept_no', 'name'])
            ->map(fn ($d) => ['dept_no' => $d->dept_no, 'name' => $d->name])
            ->all();
    }

    public function updating($name, $value)
    {
        if (
            in_array(
                $name,
                ['search', 'departmentNo', 'jabatan', 'dateFrom', 'dateTo'],
                true,
            )
        ) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->search = null;
        $this->departmentNo = null;
        $this->jabatan = null;
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetPage();
    }

    public function getCanPickDeptProperty(): bool
    {
        $user = auth()->user();

        // return ($user->is_head && $user->hasRole('PERSONALIA')) ||
        //     $user->hasRole('MANAGEMENT');
        return true;
    }

    public function getTeamStatsProperty(): array
    {
        $today = now()->toDateString();

        $totalEmployees = count($this->validNiks);
        if ($totalEmployees === 0) {
            return [
                'submitted' => 0,
                'total' => 0,
                'rate' => 0,
            ];
        }

        $submittedToday = EmployeeDailyReport::whereIn('employee_id', $this->validNiks)
            ->whereDate('work_date', $today)
            ->whereHas('employee', fn ($q) => $q->whereNull('end_date'))
            ->distinct('employee_id')
            ->count();

        return [
            'submitted' => $submittedToday,
            'total' => $totalEmployees,
            'rate' => round(($submittedToday / $totalEmployees) * 100),
        ];
    }

    public function render()
    {
        $query = \App\Infrastructure\Persistence\Eloquent\Models\Employee::query()
            ->whereNull('end_date')
            ->with(['latestDailyReport'])
            ->when(! empty($this->validNiks), fn ($q) => $q->whereIn('nik', $this->validNiks))
            // Filters
            ->when($this->departmentNo, fn ($q) => $q->where('dept_code', $this->departmentNo))
            ->when($this->jabatan, fn ($q) => $q->where('position', $this->jabatan))
            ->when($this->search, function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($w) use ($term) {
                    $w->where('nik', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhereHas('dailyReports', function ($sub) use ($term) {
                            $sub->where('work_description', 'like', $term);
                        });
                });
            })
            // Date filtering via subquery existence or relationship filter
            ->when($this->dateFrom || $this->dateTo, function ($q) {
                $q->whereHas('dailyReports', function ($sub) {
                    $from = $this->dateFrom ?: '1900-01-01';
                    $to = $this->dateTo ?: '2999-12-31';
                    $sub->whereBetween('work_date', [$from, $to]);
                });
            });

        // Optimization: Sort by latest report presence and date
        $query->leftJoin('employee_daily_reports as dr', function ($join) {
            $join->on('employees.nik', '=', 'dr.employee_id')
                ->whereRaw('dr.id = (select id from employee_daily_reports where employee_id = employees.nik order by sort_datetime desc limit 1)');
        })
            ->select('employees.*', 'dr.sort_datetime as latest_dt')
            ->orderByDesc('latest_dt');

        $employees = $query->paginate(15);

        return view('livewire.daily-reports.index', [
            'employees' => $employees,
            'canPickDept' => $this->canPickDept,
        ]);
    }
}
