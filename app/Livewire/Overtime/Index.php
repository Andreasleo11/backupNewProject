<?php

namespace App\Livewire\Overtime;

use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Domain\Overtime\Models\OvertimeForm;
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

    public function toggleDense(): void
    {
        $this->dense = ! $this->dense;
    }

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

        $fot = OvertimeForm::with('details')->findOrFail($id);

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
    public function buildStats()
    {
        // Global scope - re-run query for totals only across all filtered results
        $h = OvertimeForm::query();
        $this->scopeByRole($h);
        $this->scopeFilters($h, true);

        // Use a subquery for header ids to get correct matches
        $idsSub = $h->select('id');

        // Aggregate on details
        $row = DB::table('detail_form_overtime as d')
            ->whereIn('d.header_id', $idsSub)
            ->selectRaw("
                SUM(CASE WHEN d.status = 'Approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN d.status = 'Rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN d.status IS NULL THEN 1 ELSE 0 END) as pending
            ")
            ->first();

        $approved = (int) ($row->approved ?? 0);
        $rejected = (int) ($row->rejected ?? 0);
        $pending = (int) ($row->pending ?? 0);
        $total = max(1, $approved + $rejected + $pending);

        return [
            'approved'     => $approved,
            'rejected'     => $rejected,
            'pending'      => $pending,
            'total'        => $total,
            'pct_approved' => round(($approved * 100) / $total),
            'pct_rejected' => round(($rejected * 100) / $total),
            'pct_pending'  => round(($pending * 100) / $total),
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
        // OvertimeForm::doesntHave('details')->delete();
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
    }

    public function updatedEndDate(): void
    {
        $this->syncRangeWithDates();
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
        $q = OvertimeForm::query()
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
            ->with([
                'user:id,name',
                'department:id,name',
                // Eager-load approval steps for the inline stepper
                'approvalRequest.steps' => fn ($q) => $q
                    ->select(['id', 'approval_request_id', 'sequence', 'status', 'approver_snapshot_label'])
                    ->orderBy('sequence'),
            ])
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

    /**
     * Returns true for roles that can see org-wide analytics and advanced filters.
     * Regular staff only sees their own submissions and needs a clean, simple UI.
     */
    public function isPrivilegedUser(): bool
    {
        return Auth::user()->can('overtime.view-all');
    }

    /**
     * Returns true ONLY for the Verificator and super-admin.
     *
     * The dashboard metric cards (approved_count / rejected_count / pending_count)
     * track **detail-row** approval status — individual employee OT lines that the
     * Verificator reviews after the form's signing flow is complete. These numbers
     * are meaningless for signers (GM, Director, Dept Head) who only act on the
     * form-level workflow and never touch individual detail rows.
     */
    public function isDetailReviewer(): bool
    {
        return Auth::user()->can('overtime.review');
    }

    /**
     * Map a raw `status` slug to human-readable label + JIT-safe Tailwind classes.
     * CRITICAL: Dynamic class strings (bg-{{ $color }}-100) are purged by Tailwind JIT.
     * Always use complete pre-defined strings here.
     *
     * @return array{label: string, classes: string, icon: string}
     */
    public static function statusMeta(?string $status): array
    {
        return match ($status) {
            'approved'           => ['label' => 'Fully Approved',        'classes' => 'bg-emerald-100 text-emerald-800 border-emerald-200', 'icon' => 'bx-check-circle'],
            'rejected'           => ['label' => 'Rejected',              'classes' => 'bg-rose-100 text-rose-800 border-rose-200',         'icon' => 'bx-x-circle'],
            'waiting-dept-head'  => ['label' => 'Awaiting Dept Approval','classes' => 'bg-amber-100 text-amber-800 border-amber-200',       'icon' => 'bx-time-five'],
            'waiting-gm'         => ['label' => 'Awaiting GM Sign-off',  'classes' => 'bg-orange-100 text-orange-800 border-orange-200',    'icon' => 'bx-time-five'],
            'waiting-director'   => ['label' => 'Awaiting Director',     'classes' => 'bg-purple-100 text-purple-800 border-purple-200',    'icon' => 'bx-time-five'],
            'waiting-verificator'=> ['label' => 'Awaiting Verificator',  'classes' => 'bg-sky-100 text-sky-800 border-sky-200',             'icon' => 'bx-time-five'],
            'pending'            => ['label' => 'Pending Submission',    'classes' => 'bg-slate-100 text-slate-700 border-slate-200',       'icon' => 'bx-edit'],
            default              => ['label' => ucwords(str_replace('-', ' ', $status ?? 'Draft')), 'classes' => 'bg-slate-100 text-slate-600 border-slate-200', 'icon' => 'bx-circle'],
        };
    }

    private function scopeByRole($query)
    {
        $user = Auth::user();

        $query->where(function ($query) use ($user) {
            if ($user->hasRole('super-admin')) {
                // Super-admin sees everything; no filtering needed.
                $query->whereNotNull('status');
            } elseif ($user->can('overtime.review')) {
                $query->where(function ($subQuery) {
                    $subQuery->where('status', 'approved')->orWhere(function ($q) {
                        $q->where('status', 'waiting-dept-head')->whereHas(
                            'department',
                            fn ($qq) => $qq->where('name', 'PERSONALIA'),
                        );
                    });
                });
            } elseif ($user->hasRole('DIRECTOR') || $user->hasPermissionTo('overtime.view-all')) { // Fallback to permission if needed
                // Director/Global view
                if ($user->hasRole('DIRECTOR')) {
                     $query->where('status', 'waiting-director');
                }
            } elseif ($user->hasRole('GM')) {
                // GM sees forms that await their approval, scoped to their branch.
                $query
                    ->where('status', 'waiting-gm')
                    ->where('branch', $user->branch ?? 'Jakarta');
            } elseif ($user->hasRole('dept-head')) {
                $deptName = $user->department?->name;
                $query
                    ->whereHas('department', function ($q) use ($deptName) {
                        $q->where('name', $deptName);
                        // LOGISTIC head also covers STORE; QC head also covers QA.
                        if ($deptName === 'LOGISTIC') {
                            $q->orWhere('name', 'STORE');
                        } elseif ($deptName === 'QC') {
                            $q->orWhere('name', 'QA');
                        }
                    })
                    ->where('status', 'waiting-dept-head');
            } else {
                // Regular staff: see forms for their own department.
                $deptName = $user->department?->name;
                if ($deptName) {
                    $query->whereHas(
                        'department',
                        fn ($q) => $q->where('name', $deptName),
                    );
                }
            }

            // Always include the creator's own submissions.
            $query->orWhere('user_id', $user->id);
        });

        return $query;
    }

    public function clearFilter(string $key): void
    {
        match ($key) {
            'range'      => [$this->range = null, $this->startDate = null, $this->endDate = null],
            'dates'      => [$this->startDate = null, $this->endDate = null, $this->range = null],
            'dept'       => $this->dept = null,
            'infoStatus' => $this->infoStatus = null,
            'isPush'     => $this->isPush = null,
            'search'     => $this->search = '',
            default      => null,
        };
        $this->resetPage();
    }

    private function scopeFilters($query, bool $excludeInfoStatus = false)
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
            Auth::user()->hasRole('VERIFICATOR') &&
            ($this->isPush === '0' || $this->isPush === '1')
        ) {
            $query->where('is_push', (int) $this->isPush);
        }

        if (! $excludeInfoStatus && $this->infoStatus) {
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
                $qq->orWhere('branch', 'like', $s . '%')->orWhereHas(
                    'user',
                    fn ($u) => $u->where('name', 'like', $s . '%'),
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
            if ($user->hasRole('VERIFICATOR')) {
                $query->orderBy('first_overtime_date', 'asc');
            } else {
                $query->orderBy('id', 'desc');
            }
        }

        return $query;
    }

    private function buildQuery($q, bool $excludeInfoStatus = false)
    {
        $this->scopeByRole($q);
        $this->scopeFilters($q, $excludeInfoStatus);
        $this->applySorting($q);

        return $q;
    }

    public function render()
    {
        $user = Auth::user();
        $q = $this->buildQuery($this->baseQuery());

        $dataheader = $q->paginate($this->perPage);

        // Build stats for cards
        $stats = $this->buildStats();

        return view('livewire.overtime.index', [
            'dataheader'       => $dataheader,
            'departments'      => $this->departments,
            'user'             => Auth::user(),
            'stats'            => $stats,
            'isPrivileged'     => $this->isPrivilegedUser(),
            'isDetailReviewer' => $this->isDetailReviewer(),
        ])->layout('new.layouts.app');
    }
}

