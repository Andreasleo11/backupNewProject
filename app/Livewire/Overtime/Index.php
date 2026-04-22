<?php

namespace App\Livewire\Overtime;

use App\Application\Approval\Contracts\Approvals;
use App\Application\Overtime\Queries\OvertimeQueryBuilder;
use App\Domain\Overtime\Models\OvertimeForm;
use App\Domain\Overtime\Models\OvertimeFormDetail;
use App\Infrastructure\Persistence\Eloquent\Models\Department;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('new.layouts.app')]
class Index extends Component
{
    use WithPagination;

    // Filters in URL
    #[Url(as: 'start_date')]
    public ?string $startDate = null;

    #[Url(as: 'end_date')]
    public ?string $endDate = null;

    #[Url(as: 'dept')]
    public ?int $dept = null;

    #[Url(as: 'info_status')]
    public ?string $infoStatus = null; // pending|approved|rejected|null

    #[Url(as: 'hide_signed')]
    public bool $hideSigned = true; // Default hide items I already signed

    // UI state
    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'per_page')]
    public int $perPage = 10;

    #[Url(as: 'sort')]
    public string $sortField = 'id';

    #[Url(as: 'dir')]
    public string $sortDirection = 'desc';

    #[Url(as: 'range')]
    public ?string $range = null; // 'today'|'7d'|'30d'|'mtd'|null

    public array $departments = [];

    public ?int $pendingDeleteId = null;

    // Selection & Bulk Action State
    public array $selectedIds = [];

    public bool $showSnapshot = false;

    public array $snapshot = [];

    public array $warnings = [];

    public bool $isProcessingBulk = false;

    #[On('confirm-delete')]
    public function confirmDelete(int $id): void
    {
        $this->pendingDeleteId = $id;
        $this->dispatch('show-delete-modal');
    }

    public function deleteConfirmed(): void
    {
        $id = $this->pendingDeleteId;
        if (! $id) {
            return;
        }

        $fot = OvertimeForm::with('details')->findOrFail($id);
        $this->authorize('delete', $fot);

        $fot->details()->delete();
        $fot->delete();

        $this->pendingDeleteId = null;
        $this->dispatch('hide-delete-modal');
        $this->dispatch('flash', type: 'success', message: "Form Overtime #{$id} deleted.");
        $this->resetPage();
    }

    public function setInfoFilter(string $status): void
    {
        $this->infoStatus = $status;
        $this->resetPage();
    }

    public function buildStats(): array
    {
        $builder = new OvertimeQueryBuilder;
        $h = $builder->build(Auth::user(), array_merge($this->getFilterParams(), [
            'excludeInfoStatus' => true,
        ]));

        $row = OvertimeForm::query()
            ->workflowApproved()
            ->join('detail_form_overtime as d', 'd.header_id', '=', 'header_form_overtime.id')
            ->whereIn('header_form_overtime.id', function ($query) use ($h) {
                $query->select('id')->fromSub($h->select('id'), 'sub');
            })
            ->whereNull('d.deleted_at')
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

        $myApprovalCount = (new OvertimeQueryBuilder)->build(Auth::user(), ['infoStatus' => 'my_approval'])->count();

        return [
            'approved' => $approved,
            'rejected' => $rejected,
            'pending' => $pending,
            'total' => $total,
            'pct_approved' => round(($approved * 100) / $total),
            'pct_rejected' => round(($rejected * 100) / $total),
            'pct_pending' => round(($pending * 100) / $total),
            'my_approval_count' => $myApprovalCount,
        ];
    }

    protected function rules(): array
    {
        return [
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date'],
            'infoStatus' => ['nullable', Rule::in(['pending', 'approved', 'rejected', 'my_approval'])],
            'perPage' => ['integer', Rule::in([10, 25, 50])],
            'sortField' => ['string', Rule::in(['id', 'first_overtime_date', 'status', 'workflow_status'])],
            'sortDirection' => ['string', Rule::in(['asc', 'desc'])],
        ];
    }

    public function updated($name, $value): void
    {
        if (in_array($name, ['startDate', 'endDate', 'dept', 'infoStatus', 'search', 'perPage', 'sortField', 'sortDirection', 'hideSigned'])) {
            $this->resetPage();
            $this->selectedIds = [];
        }

        if (in_array($name, ['startDate', 'endDate'])) {
            $this->validateDates();
            $this->syncRangeWithDates();
        }
    }

    private function validateDates(): void
    {
        $this->resetErrorBag(['startDate', 'endDate']);
        $this->validate(['startDate' => 'nullable|date', 'endDate' => 'nullable|date']);

        if ($this->startDate && $this->endDate && $this->endDate < $this->startDate) {
            $this->addError('endDate', 'End Date must be after or equal to Start Date.');
        }
    }

    public function mount(): void
    {
        $this->authorize('viewAny', OvertimeForm::class);
        $this->departments = Department::select('id', 'name')->orderBy('name')->get()->toArray();

        // Auto-redirect to "My Approvals" for high-level oversight roles
        // Only trigger if no explicit filter is given.
        if (empty($this->infoStatus)) {
            $user = Auth::user();
            if ($user->hasAnyRole(['department-head', 'general-manager', 'verificator', 'director'])) {
                $myApprovalCount = app(OvertimeQueryBuilder::class)
                    ->build($user, ['infoStatus' => 'my_approval'])
                    ->count();

                if ($myApprovalCount >= 0) {
                    $this->infoStatus = 'my_approval';
                }
            }
        }
    }

    public function resetFilters(): void
    {
        $this->startDate = null;
        $this->endDate = null;
        $this->dept = null;
        $this->infoStatus = null;
        $this->search = '';
        $this->range = null;
        $this->hideSigned = true;
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if (! in_array($field, ['id', 'first_overtime_date', 'workflow_status'], true)) {
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
            '7d' => [now()->subDays(6)->toDateString(), $today],
            '30d' => [now()->subDays(29)->toDateString(), $today],
            'mtd' => [now()->startOfMonth()->toDateString(), $today],
            default => [null, null],
        };

        $this->range = $preset;
        $this->startDate = $start;
        $this->endDate = $end;
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

    public function clearFilter(string $key): void
    {
        match ($key) {
            'range' => [$this->range = null, $this->startDate = null, $this->endDate = null],
            'dates' => [$this->startDate = null, $this->endDate = null, $this->range = null],
            'dept' => $this->dept = null,
            'infoStatus' => $this->infoStatus = null,
            'search' => $this->search = '',
            default => null,
        };
        $this->resetPage();
    }

    public function exportCsv()
    {
        $this->authorize('export', OvertimeForm::class);

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'ID', 'Admin', 'Dept', 'Branch', 'First Overtime Date', 'Status',
                'Type', 'After Hour', 'Approved', 'Rejected', 'Pending', 'Created At',
            ]);

            $builder = new OvertimeQueryBuilder;
            $query = $builder->build(Auth::user(), $this->getFilterParams());

            $query->orderBy('id', 'desc')->chunk(1000, function ($rows) use ($out) {
                foreach ($rows as $fot) {
                    fputcsv($out, [
                        $fot->id, optional($fot->user)->name, optional($fot->department)->name, $fot->branch,
                        optional($fot->first_overtime_date)?->format('Y-m-d'), $fot->workflow_status,
                        $fot->is_planned ? 'Planned' : 'Urgent', $fot->is_after_hour ? 'Yes' : 'No',
                        $fot->approved_count, $fot->rejected_count, $fot->pending_count, $fot->created_at?->format('Y-m-d'),
                    ]);
                }
            });
            fclose($out);
        }, 'form-overtime.csv', ['Content-Type' => 'text/csv']);
    }

    public function isPrivilegedUser(): bool
    {
        $user = Auth::user();

        return $user->hasAnyRole(['super-admin', 'director', 'general-manager']) ||
               $user->can('overtime.view-all') ||
               $user->can('approval.view-all');
    }

    public function isDetailReviewer(): bool
    {
        return Auth::user()->can('overtime.review');
    }

    /**
     * Decision Intelligence: Load a detailed snapshot of the selected batch.
     */
    public function loadSnapshot(): void
    {
        if (empty($this->selectedIds)) {
            return;
        }

        $this->isProcessingBulk = true;

        $details = OvertimeFormDetail::whereIn('header_id', $this->selectedIds)->get();
        $headers = OvertimeForm::with('department')->whereIn('id', $this->selectedIds)->get();

        $totalMinutes = 0;
        foreach ($details as $d) {
            try {
                $start = Carbon::parse($d->start_date . ' ' . $d->start_time);
                $end = Carbon::parse($d->end_date . ' ' . $d->end_time);
                $diff = $start->diffInMinutes($end);
                $totalMinutes += max(0, $diff - (int) $d->break);
            } catch (\Exception $e) {
            }
        }

        $this->snapshot = [
            'total_forms' => count($this->selectedIds),
            'total_employees' => $details->pluck('NIK')->unique()->count(),
            'total_hours' => round($totalMinutes / 60, 1),
            'date_range' => [
                'start' => $details->min('overtime_date'),
                'end' => $details->max('overtime_date'),
            ],
            'departments' => $headers->pluck('department.name')->filter()->countBy()->toArray(),
        ];

        $this->calculateHeuristicWarnings($details);

        $this->isProcessingBulk = false;
        $this->showSnapshot = true;
    }

    private function calculateHeuristicWarnings($details): void
    {
        $this->warnings = [];

        // 1. Session Overlap Check (within selected batch)
        $overlaps = [];
        $byNik = $details->groupBy('NIK');

        foreach ($byNik as $nik => $rows) {
            if ($rows->count() < 2) {
                continue;
            }

            // Basic sort by start time
            $sorted = $rows->sortBy(fn ($r) => $r->start_date . ' ' . $r->start_time);
            $prev = null;

            foreach ($sorted as $current) {
                if ($prev) {
                    $prevEnd = Carbon::parse($prev->end_date . ' ' . $prev->end_time);
                    $currStart = Carbon::parse($current->start_date . ' ' . $current->start_time);

                    if ($currStart->lt($prevEnd)) {
                        $overlaps[] = "Employee #{$nik} ({$current->name}) has overlapping sessions on " . Carbon::parse($current->start_date)->format('d M');
                        break; // Only report once per employee to avoid spam
                    }
                }
                $prev = $current;
            }
        }

        if (! empty($overlaps)) {
            $this->warnings['overlaps'] = array_slice($overlaps, 0, 5); // Limit to top 5
            if (count($overlaps) > 5) {
                $this->warnings['overlaps'][] = '...and ' . (count($overlaps) - 5) . ' more conflicts.';
            }
        }

        // 2. High Intensity Check (>12 hours in a single form/day)
        $highIntensity = $details->filter(function ($d) {
            try {
                $start = Carbon::parse($d->start_date . ' ' . $d->start_time);
                $end = Carbon::parse($d->end_date . ' ' . $d->end_time);

                return ($start->diffInHours($end) - ($d->break / 60)) > 12;
            } catch (\Exception) {
                return false;
            }
        });

        if ($highIntensity->count() > 0) {
            $this->warnings['intensity'] = "{$highIntensity->count()} sessions exceed 12 hours of duration.";
        }
    }

    public function bulkApprove(): void
    {
        $this->processBulkAction('approve');
    }

    public function bulkReject(): void
    {
        $this->processBulkAction('reject');
    }

    private function processBulkAction(string $action): void
    {
        $approvalService = app(Approvals::class);
        $userId = Auth::id();
        $successCount = 0;
        $failCount = 0;

        foreach ($this->selectedIds as $id) {
            try {
                $form = OvertimeForm::findOrFail($id);
                if ($approvalService->canAct($form, $userId)) {
                    if ($action === 'approve') {
                        $approvalService->approve($form, $userId, 'Bulk approved via Dashboard');
                    } else {
                        $approvalService->reject($form, $userId, 'Bulk rejected via Dashboard');
                    }
                    $successCount++;
                } else {
                    $failCount++;
                }
            } catch (\Exception $e) {
                $failCount++;
            }
        }

        $this->selectedIds = [];
        $this->showSnapshot = false;

        $msg = "Batch {$action} complete. {$successCount} processed, {$failCount} skipped/failed.";
        $this->dispatch('flash', type: $successCount > 0 ? 'success' : 'error', message: $msg);
    }

    private function getFilterParams(): array
    {
        return [
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'dept' => $this->dept,
            'infoStatus' => $this->infoStatus,
            'search' => $this->search,
            'hideSigned' => $this->hideSigned,
        ];
    }

    public function render()
    {
        $builder = new OvertimeQueryBuilder;
        $query = $builder->build(Auth::user(), $this->getFilterParams());

        // Sorting
        $query->reorder();
        if ($this->sortField === 'id' && $this->sortDirection === 'desc' && Auth::user()->hasRole('verificator')) {
            $query->orderBy('first_overtime_date', 'asc');
        } else {
            // Fix workflow_status sort if it relies on a mutator, mapping it to status
            $sortColumn = $this->sortField === 'workflow_status' ? 'status' : $this->sortField;
            $query->orderBy($sortColumn, $this->sortDirection);
        }

        $dataheader = $query->paginate($this->perPage);

        return view('livewire.overtime.index', [
            'dataheader' => $dataheader,
            'departments' => $this->departments,
            'user' => Auth::user(),
            'stats' => $this->isDetailReviewer() ? $this->buildStats() : ['my_approval_count' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0, 'pct_approved' => 0, 'pct_rejected' => 0, 'pct_pending' => 0],
            'isPrivileged' => $this->isPrivilegedUser(),
            'isDetailReviewer' => $this->isDetailReviewer(),
            'canApprove' => Auth::user()->can('overtime.approve'),
        ]);
    }
}
