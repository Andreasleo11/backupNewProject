<?php

// app/Livewire/Verification/Index.php

namespace App\Livewire\Verification;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'tailwind';

    public string $status = 'all';

    public string $search = '';

    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    public string $filterDept = '';

    public string $filterCreator = '';

    public string $filterRecStart = '';

    public string $filterRecEnd = '';

    protected $queryString = [
        'status',
        'search',
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
        'filterDept' => ['except' => ''],
        'filterCreator' => ['except' => ''],
        'filterRecStart' => ['except' => ''],
        'filterRecEnd' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDept(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCreator(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRecStart(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRecEnd(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function render()
    {
        $counts = VerificationReport::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusCounts = [
            'DRAFT' => $counts['DRAFT'] ?? 0,
            'IN_REVIEW' => $counts['IN_REVIEW'] ?? 0,
            'APPROVED' => $counts['APPROVED'] ?? 0,
            'REJECTED' => $counts['REJECTED'] ?? 0,
            'ALL' => array_sum($counts),
        ];

        $departments = [];
        $users = [];
        if (auth()->check() && auth()->user()->hasRole('super-admin')) {
            $departments = Department::orderBy('name')->get();
            $users = User::orderBy('name')->get();
        }

        $validSortFields = ['id', 'document_number', 'customer', 'invoice_number', 'rec_date', 'verify_date', 'status', 'total_value'];
        $sort = in_array($this->sortField, $validSortFields) ? $this->sortField : 'id';
        $direction = in_array($this->sortDirection, ['asc', 'desc']) ? $this->sortDirection : 'desc';

        $q = VerificationReport::query()
            ->select('verification_reports.*')
            ->selectRaw('(SELECT SUM(verify_quantity * price) FROM verification_items WHERE verification_items.verification_report_id = verification_reports.id) as total_value')
            ->when(
                $this->status !== 'all',
                fn (Builder $query) => $query->where('status', $this->status)
            )
            ->when($this->search, function (Builder $query) {
                $s = "%{$this->search}%";
                $query->where(function ($q) use ($s) {
                    $q->where('document_number', 'like', $s)
                        ->orWhere('customer', 'like', $s)
                        ->orWhere('invoice_number', 'like', $s);
                });
            })
            ->when($this->filterDept && auth()->user()?->hasRole('super-admin'), function (Builder $query) {
                $query->where('meta->department', $this->filterDept);
            })
            ->when($this->filterCreator && auth()->user()?->hasRole('super-admin'), function (Builder $query) {
                $query->where('creator_id', $this->filterCreator);
            })
            ->when($this->filterRecStart && auth()->user()?->hasRole('super-admin'), function (Builder $query) {
                $query->whereDate('rec_date', '>=', $this->filterRecStart);
            })
            ->when($this->filterRecEnd && auth()->user()?->hasRole('super-admin'), function (Builder $query) {
                $query->whereDate('rec_date', '<=', $this->filterRecEnd);
            })
            ->orderBy($sort, $direction);

        return view('livewire.verification.index', [
            'reports' => $q->paginate(10),
            'statusCounts' => $statusCounts,
            'departments' => $departments,
            'users' => $users,
        ]);
    }
}
