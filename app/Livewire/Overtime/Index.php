<?php

namespace App\Livewire\Overtime;

use App\Models\Department;
use App\Models\HeaderFormOvertime;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    // Filters in URL
    #[Url(as: 'start_date')]
    public ?string $startDate = null;

    #[Url(as: 'end_date')]
    public ?string $endDate = null;

    #[Url(as: 'dept')]
    public ?int $dept = null;

    #[Url(as: 'info_status')]
    public ?string $infoStatus = null; // pending|approved|rejected|null

    #[Url(as: 'is_push')]
    public ?string $isPush = null; // "1" | "0" | null

    // UI state
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'per_page')]
    public int $perPage = 10;

    #[Url(as: 'sort')]
    public string $sortField = 'id';

    #[Url(as: 'dir')]
    public string $sortDirection = 'desc';

    #[Url(as: 'dense')]
    public bool $dense = false;

    #[Url(as: 'range')]
    public ?string $range = null; // 'today'|'7d'|'30d'|'mtd'|null

    public array $departments = [];

    public string $statsScope = 'all'; // 'all' or 'page'

    public ?int $pendingDeleteId = null; // fired from the Delete button

    #[On('confirm-delete')]
    public function confirmDelete(int $id): void
    {
        $this->pendingDeleteId = $id;

        // Tell the frontend to open the modal
        $this->dispatch('show-delete-modal');
    }

    public function deleteConfirmed(): void
    {
        $id = $this->pendingDeleteId;
        if (! $id) {
            return;
        }

        $fot = HeaderFormOvertime::with('details')->findOrFail($id);

        // (Optional) Gate/Policy check
        // Gate::authorize('delete', $fot);

        // If FK doesn't cascade, do it here:
        $fot->details()->delete();

        $fot->delete();

        $this->pendingDeleteId = null;

        // Close modal + toast + refresh page/pagination
        $this->dispatch('hide-delete-modal');
        $this->dispatch('toast', message: "Form Overtime #{$id} deleted.");
        $this->resetPage();
    }

    /**
     * Clicking a card applies the info status filter.
     */
    public function setInfoFilter(string $status): void
    {
        $this->infoStatus = $status;
        $this->resetPage();
    }

    /**
     * Build stats that respect current filters/role.
     * - When scope = 'page', compute from the current page collection (fast and simple).
     * - When scope = 'all', do one aggregate query across all filtered results (efficient).
     */
    public function buildStats(LengthAwarePaginator $page)
    {
        if ($this->statsScope === 'page') {
            $approved = (int) $page->sum('approved_count');
            $rejected = (int) $page->sum('rejected_count');
            $pending = (int) $page->sum('pending_count');
        } else {
            // Aggregate across ALL filtered results using a single SQL
            // 1) Rebuild the filtered header query (no order/pagination)
            $h = HeaderFormOvertime::query();
            $this->scopeByRole($h);
            $this->scopeFilters($h);

            // 2) Use a subquery for header ids
            $idsSub = $h->select('id');

            // 3) Aggregate on details + join headers for 'urgent'
            $row = DB::table('detail_form_overtime as d')
                ->join('header_form_overtime as h', 'h.id', '=', 'd.header_id')
                ->whereIn('d.header_id', $idsSub)
                ->selectRaw(
                    "
                SUM(CASE WHEN d.status = 'Approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN d.status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN d.status IS NULL THEN 1 ELSE 0 END) as pending
            ",
                )
                ->first();

            $approved = (int) ($row->approved ?? 0);
            $rejected = (int) ($row->rejected ?? 0);
            $pending = (int) ($row->pending ?? 0);
        }

        $total = max(1, $approved + $rejected + $pending); // avoid /0

        return [
            'approved' => $approved,
            'rejected' => $rejected,
            'pending' => $pending,
            'total' => $total,
            'pct_approved' => round(($approved * 100) / $total),
            'pct_rejected' => round(($rejected * 100) / $total),
            'pct_pending' => round(($pending * 100) / $total),
        ];
    }

    protected function rules(): array
    {
        return [
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date'],
            'infoStatus' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'isPush' => ['nullable', Rule::in(['0', '1'])],
            'perPage' => ['integer', Rule::in([10, 25, 50])],
            'sortField' => ['string', Rule::in(['id', 'first_overtime_date', 'status'])],
            'sortDirection' => ['string', Rule::in(['asc', 'desc'])],
        ];
    }

    // Auto-apply: reset page on any relevant change
    public function updated($name, $value): void
    {
        if (
            in_array($name, [
                'startDate',
                'endDate',
                'dept',
                'infoStatus',
                'isPush',
                'search',
                'perPage',
                'sortField',
                'sortDirection',
            ])
        ) {
            $this->resetPage();
        }

        // validate date order when both are present
        if (in_array($name, ['startDate', 'endDate'])) {
            $this->validateDates();
        }
    }

    private function validateDates(): void
    {
        $this->resetErrorBag(['startDate', 'endDate']);
        $this->validate([
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
        ]);

        if ($this->startDate && $this->endDate && $this->endDate < $this->startDate) {
            $this->addError('endDate', 'End Date must be after or equal to Start Date.');
        }
    }

    public function mount(): void
    {
        // ⚠️ Move heavy cleanup out of request cycle:
        // HeaderFormOvertime::doesntHave('details')->delete();
        // Put it in a nightly job/queue instead.

        $this->departments = Department::select('id', 'name')->orderBy('name')->get()->toArray();
    }

    public function resetFilters(): void
    {
        $this->startDate = null;
        $this->endDate = null;
        $this->dept = null;
        $this->infoStatus = null;
        $this->isPush = null;
        $this->search = '';
        $this->range = null;
        $this->infoStatus = null;
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['id', 'first_overtime_date', 'status'], true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function setRange(string $preset): void
    {
        // Clicking the same preset toggles it off
        if ($this->range === $preset) {
            $this->range = null;
            $this->startDate = null;
            $this->endDate = null;
            $this->resetPage();

            return;
        }

        $today = now()->toDateString();

        [$start, $end] = match ($preset) {
            'today' => [$today, $today],
            '7d' => [
                now()->subDays(6)->toDateString(),
                $today,
            ], // inclusive last 7 (today + 6 back)
            '30d' => [now()->subDays(29)->toDateString(), $today], // inclusive last 30
            'mtd' => [now()->startOfMonth()->toDateString(), $today],
            default => [null, null],
        };

        $this->range = $preset;
        $this->startDate = $start;
        $this->endDate = $end;
        $this->resetPage();
    }

    public function updatedStartDate(): void
    {
        $this->syncRangeWithDates();
        $this->resetPage();
    }

    public function updatedEndDate(): void
    {
        $this->syncRangeWithDates();
        $this->resetPage();
    }

    private function syncRangeWithDates(): void
    {
        if (! $this->startDate || ! $this->endDate) {
            $this->range = null;

            return;
        }

        $today = now()->toDateString();
        $ranges = [
            'today' => [$today, $today],
            '7d' => [now()->subDays(6)->toDateString(), $today],
            '30d' => [now()->subDays(29)->toDateString(), $today],
            'mtd' => [now()->startOfMonth()->toDateString(), $today],
        ];

        $this->range = null;
        foreach ($ranges as $key => [$s, $e]) {
            if ($this->startDate === $s && $this->endDate === $e) {
                $this->range = $key;
                break;
            }
        }
    }

    public function exportCsv()
    {
        // Stream the current filtered result set.
        return response()->streamDownload(
            function () {
                $out = fopen('php://output', 'w');
                // header row
                fputcsv($out, [
                    'ID',
                    'Admin',
                    'Dept',
                    'Branch',
                    'First Overtime Date',
                    'Status',
                    'Type',
                    'After Hour',
                    'Approved',
                    'Rejected',
                    'Pending',
                    'Created At',
                ]);

                $this->buildQuery(clone $this->baseQuery())
                    ->orderBy('id', 'desc') // deterministic stream order
                    ->chunk(1000, function ($rows) use ($out) {
                        foreach ($rows as $fot) {
                            fputcsv($out, [
                                $fot->id,
                                optional($fot->user)->name,
                                optional($fot->department)->name,
                                $fot->branch,
                                optional($fot->first_overtime_date)?->format('Y-m-d'),
                                $fot->status,
                                $fot->is_planned ? 'Planned' : 'Urgent',
                                $fot->is_after_hour ? 'Yes' : 'No',
                                $fot->approved_count,
                                $fot->rejected_count,
                                $fot->pending_count,
                                $fot->created_at?->format('Y-m-d'),
                            ]);
                        }
                    });

                fclose($out);
            },
            'form-overtime.csv',
            [
                'Content-Type' => 'text/csv',
            ],
        );
    }

    private function baseQuery()
    {
        // Minimal columns from header to render table
        $q = HeaderFormOvertime::query()
            ->select([
                'id',
                'user_id',
                'dept_id',
                'branch',
                'status',
                'is_push',
                'is_planned',
                'is_after_hour',
                'created_at',
            ])
            ->with(['user:id,name', 'department:id,name'])
            // counts for the Info column (fast subselects)
            ->withCount([
                'details as approved_count' => fn ($q) => $q->where('status', 'Approved'),
                'details as rejected_count' => fn ($q) => $q->where('status', 'Rejected'),
                'details as pending_count' => fn ($q) => $q->whereNull('status'),
            ]);

        // precompute one consistent earliest date for sort & display
        $q->selectSub(function ($sub) {
            $sub->from('detail_form_overtime as d')
                ->selectRaw('MIN(d.start_date)')
                ->whereColumn('d.header_id', 'header_form_overtime.id');
        }, 'first_overtime_date');

        return $q;
    }

    private function scopeByRole($query)
    {
        $user = Auth::user();

        $query->where(function ($query) use ($user) {
            if ($user->role->name === 'SUPERADMIN') {
                $query->whereNotNull('status');

                $overtimeforms = HeaderFormOvertime::whereHas(
                    'user',
                    fn ($q) => $q->where('name', 'ani'),
                )
                    ->whereHas('department', fn ($q) => $q->where('name', 'BUSINESS'))
                    ->where('status', '!=', 'waiting-creator')
                    ->get();

                $andriani = \App\Models\User::where('name', 'andriani')->first();

                foreach ($overtimeforms as $form) {
                    foreach ($form->approvals as $approval) {
                        if ($approval->step->role_slug === 'creator') {
                            $approval->approver_id = $andriani->id;
                            $approval->signature_path = 'andriani.png';
                            $approval->save();
                        }
                    }

                    $form->user_id = $andriani->id;
                    $form->saveQuietly();
                }
            } elseif ($user->specification->name === 'VERIFICATOR') {
                $query->where(function ($subQuery) {
                    $subQuery->where('status', 'approved')->orWhere(function ($q) {
                        $q->where('status', 'waiting-dept-head')->whereHas(
                            'department',
                            fn ($qq) => $qq->where('name', 'PERSONALIA'),
                        );
                    });
                });
            } elseif ($user->specification->name === 'DIRECTOR') {
                $query->where('status', 'waiting-director');
            } elseif ($user->is_gm) {
                $query
                    ->where('status', 'waiting-gm')
                    ->where('branch', $user->name === 'pawarid' ? 'Karawang' : 'Jakarta');
            } elseif ($user->is_head) {
                $query
                    ->whereHas('department', function ($q) use ($user) {
                        $q->where('dept_id', $user->department->id);
                        if ($user->department->name === 'LOGISTIC') {
                            $q->orWhere('name', 'STORE');
                        }
                    })
                    ->where('status', 'waiting-dept-head');
            } else {
                if ($user->name === 'Umi') {
                    $query->whereHas('department', fn ($q) => $q->whereIn('name', ['QA', 'QC']));
                } elseif ($user->name === 'nurul') {
                    $query->whereHas(
                        'department',
                        fn ($q) => $q->whereIn('name', ['PLASTIC INJECTION', 'MAINTENANCE MACHINE']),
                    );
                } else {
                    $query->whereHas(
                        'department',
                        fn ($q) => $q->where('name', $user->department->name),
                    );
                }
            }

            // Always include creator's own entries (kept inside the role group)
            $query->orWhere('user_id', $user->id);
        });

        return $query;
    }

    private function scopeFilters($query)
    {
        if ($this->startDate && $this->endDate) {
            $start = $this->startDate;
            $end = $this->endDate;
            $query->whereHas(
                'details',
                fn ($q) => $q
                    ->whereDate('start_date', '>=', $start)
                    ->whereDate('start_date', '<=', $end),
            );
        }

        if ($this->dept) {
            $query->where('dept_id', $this->dept);
        }

        if (
            Auth::user()->specification->name === 'VERIFICATOR' &&
            ($this->isPush === '0' || $this->isPush === '1')
        ) {
            $query->where('is_push', (int) $this->isPush);
        }

        if ($this->infoStatus) {
            $status = $this->infoStatus;
            $query->whereHas('details', function ($q) use ($status) {
                $status === 'pending'
                    ? $q->whereNull('status')
                    : $q->where('status', ucfirst($status)); // Approved/Rejected
            });
        }

        // Smarter search: numeric => exact id; text => prefix for indexes
        if ($this->search !== '') {
            $s = trim($this->search);
            $query->where(function ($qq) use ($s) {
                if (ctype_digit($s)) {
                    $qq->orWhere('id', (int) $s);
                }
                $qq->orWhere('branch', 'like', $s.'%')->orWhereHas(
                    'user',
                    fn ($u) => $u->where('name', 'like', $s.'%'),
                );
            });
        }

        return $query;
    }

    private function applySorting($query)
    {
        // Clear any previous ORDER BY
        $query->reorder();

        // Default if user hasn’t chosen anything: verifier by earliest date asc, otherwise id desc
        $user = Auth::user();
        $manual = ! ($this->sortField === 'id' && $this->sortDirection === 'desc');

        if ($manual) {
            $query->orderBy($this->sortField, $this->sortDirection);
        } else {
            if ($user->specification->name === 'VERIFICATOR') {
                $query->orderBy('first_overtime_date', 'asc');
            } else {
                $query->orderBy('id', 'desc');
            }
        }

        return $query;
    }

    private function buildQuery($q)
    {
        $this->scopeByRole($q);
        $this->scopeFilters($q);
        $this->applySorting($q);

        return $q;
    }

    public function render()
    {
        $user = Auth::user();
        $q = $this->buildQuery($this->baseQuery());

        $dataheader = $q->paginate($this->perPage);

        // Build stats for cards
        $stats = $this->buildStats($dataheader);

        return view('livewire.overtime.index', [
            'dataheader' => $dataheader,
            'departments' => $this->departments,
            'user' => Auth::user(),
            'stats' => $stats,
        ]);
    }
}
