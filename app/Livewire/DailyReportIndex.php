<?php

namespace App\Livewire;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DailyReportIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Query-string synced filters
    #[Url(as: 'q')]
    public ?string $search = null;

    #[Url(as: 'filter_employee_id')]
    public ?string $employeeId = null;

    #[Url(as: 'filter_department_no')]
    public ?string $departmentNo = null;

    #[Url(as: 'filter_jabatan')]
    public ?string $jabatan = null;

    #[Url(as: 'from')]
    public ?string $dateFrom = null; // YYYY-MM-DD

    #[Url(as: 'to')]
    public ?string $dateTo = null; // YYYY-MM-DD

    public array $employeesDropdown = [];

    public array $departmentNos = []; // [['dept_no'=>..., 'name'=>...], ...]

    public array $positions = []; // unique jabatan list

    /** @var array<string> */
    protected array $validNiks = [];

    public function mount()
    {
        $user = auth()->user();

        // Authorization (same logic as controller)
        if (! $user->is_head && $user->specification->name !== 'DIRECTOR') {
            abort(403, 'Anda tidak memiliki akses');
        }

        // Scope employees to department (unless Bernadett or DIRECTOR)
        $employeeQuery = Employee::query();
        if ($user->name !== 'Bernadett' && $user->specification->name !== 'DIRECTOR') {
            $employeeQuery->where('Dept', $user->department->dept_no);
        }

        $validEmployees = $employeeQuery->get(['NIK', 'Nama', 'jabatan', 'Dept']);
        $this->validNiks = $validEmployees->pluck('NIK')->all();

        // Dropdowns
        $this->employeesDropdown = $validEmployees
            ->map(fn ($e) => ['employee_id' => $e->NIK, 'employee_name' => $e->Nama])
            ->values()
            ->all();

        $this->positions = $validEmployees
            ->pluck('jabatan')
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
                ['search', 'employeeId', 'departmentNo', 'jabatan', 'dateFrom', 'dateTo'],
                true,
            )
        ) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->search = null;
        $this->employeeId = null;
        $this->departmentNo = null;
        $this->jabatan = null;
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->resetPage();
    }

    public function getCanPickDeptProperty(): bool
    {
        $user = auth()->user();

        return ($user->is_head && $user->department->name === 'PERSONALIA') ||
            $user->department->name === 'MANAGEMENT';
    }

    public function render()
    {
        // SQL that safely extracts the "end time" when you have a range "HH:MM - HH:MM",
        // otherwise uses the single time. Works on MySQL/MariaDB.
        $normalizedTimeSql = "
            CASE
                WHEN dr.work_time LIKE '%-%'
                    THEN TRIM(SUBSTRING_INDEX(dr.work_time, '-', -1))  -- take end time
                ELSE dr.work_time
            END
        ";

        // Subquery to compute the latest datetime per employee (no N+1)
        $latestSub = DB::table('employee_daily_reports as dr')
            ->select([
                'dr.employee_id',
                DB::raw("MAX(CONCAT(dr.work_date, ' ', $normalizedTimeSql)) as latest_dt"),
            ])
            ->groupBy('dr.employee_id');

        $base = DB::table('employee_daily_reports as dr')
            ->join('employees as e', function ($join) {
                $join
                    ->on('dr.employee_id', '=', 'e.NIK')
                    ->whereColumn('dr.employee_name', 'e.Nama')
                    ->whereColumn('dr.departement_id', 'e.Dept');
            })
            ->leftJoinSub(
                $latestSub,
                'lr',
                fn ($join) => $join->on('lr.employee_id', '=', 'dr.employee_id'),
            )
            ->when(! empty($this->validNiks), fn ($q) => $q->whereIn('e.NIK', $this->validNiks))
            // Filters
            ->when($this->employeeId, fn ($q) => $q->where('dr.employee_id', $this->employeeId))
            ->when(
                $this->departmentNo,
                fn ($q) => $q->where('dr.departement_id', $this->departmentNo),
            )
            ->when($this->jabatan, fn ($q) => $q->where('e.jabatan', $this->jabatan))
            ->when($this->search, function ($q) {
                $term = '%'.trim($this->search).'%';
                $q->where(function ($w) use ($term) {
                    $w->where('dr.employee_id', 'like', $term)->orWhere('e.Nama', 'like', $term);
                });
            })
            ->when($this->dateFrom || $this->dateTo, function ($q) {
                $from = $this->dateFrom ?: '1900-01-01';
                $to = $this->dateTo ?: '2999-12-31';
                $q->whereBetween('dr.work_date', [$from, $to]);
            })
            // Group by employee (one row per employee)
            ->groupBy('dr.employee_id', 'dr.departement_id', 'e.Nama', 'e.jabatan', 'lr.latest_dt')
            ->orderByDesc('lr.latest_dt')
            ->select([
                'dr.employee_id',
                'dr.departement_id',
                'e.jabatan',
                DB::raw('MIN(e.Nama) as employee_name'),
                DB::raw('MAX(lr.latest_dt) as latest_dt'),
            ]);

        $employees = $base->paginate(15)->withQueryString();

        return view('livewire.daily-report-index', [
            'employees' => $employees,
            'canPickDept' => $this->canPickDept,
        ]);
    }
}
