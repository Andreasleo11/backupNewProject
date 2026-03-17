<?php

namespace App\Livewire\MonthlyBudgetSummary;

use App\Domain\MonthlyBudget\Services\BudgetSummaryService;
use App\Models\MonthlyBudgetSummaryReport as Report;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // URL-synced filters (back-button friendly & shareable)
    #[Url(as: 'q', keep: true)]
    public ?string $search = null;

    #[Url(keep: true)]
    public ?string $status = null; // e.g. "1","2","3","4","5","6" or null (All)

    #[Url(as: 'from', keep: true)]
    public ?string $monthFrom = null; // "mm-yyyy"

    #[Url(as: 'to', keep: true)]
    public ?string $monthTo = null; // "mm-yyyy"

    // Sorting
    #[Url(keep: true)]
    public string $sortField = 'created_at';

    #[Url(keep: true)]
    public string $sortDirection = 'desc'; // "asc" | "desc"

    // Page size (optional tweakable)
    #[Url(keep: true)]
    public int $perPage = 10;

    // For header CTA
    public bool $showGenerateButton = false;

    // Whitelist sort fields -> db columns
    private array $sortable = [
        'doc_num' => 'doc_num',
        'report_date' => 'report_date',
        'created_at' => 'created_at',
        'total' => 'total_amount', // comes from selectSub (withTotals)
    ];

    public function mount(): void
    {
        $u = auth()->user();
        $this->showGenerateButton =
            ! $u->is_head && ! $u->is_gm && $u->department?->name !== 'MANAGEMENT';
    }

    public function updating($name): void
    {
        // Any filter change -> go back to first page
        if (in_array($name, ['search', 'status', 'monthFrom', 'monthTo', 'perPage'])) {
            $this->resetPage();
        }
    }

    public function sortBy(string $field): void
    {
        if (! isset($this->sortable[$field])) {
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

    public function clearFilters(): void
    {
        $this->search = null;
        $this->status = null;
        $this->monthFrom = null;
        $this->monthTo = null;
        $this->resetPage();
    }

    private function normalizeMonth(?string $mmYYYY): ?string
    {
        if (! $mmYYYY) {
            return null;
        }
        // Accept both "mm-yyyy" and "yyyy-mm"
        $mmYYYY = trim($mmYYYY);
        if (preg_match('/^\d{2}-\d{4}$/', $mmYYYY)) {
            [$m, $y] = explode('-', $mmYYYY);

            return sprintf('%04d-%02d-01', (int) $y, (int) $m);
        }
        if (preg_match('/^\d{4}-\d{2}$/', $mmYYYY)) {
            [$y, $m] = explode('-', $mmYYYY);

            return sprintf('%04d-%02d-01', (int) $y, (int) $m);
        }

        return null;
    }

    public function cloneReport(int $id, BudgetSummaryService $service): void
    {
        // For simplicity, we clone to the current month if not specified, 
        // but typically the service might handle logic or we could prompt.
        // For now, let's clone to "next month" relative to the source.
        $source = Report::find($id);
        if (! $source) {
            session()->flash('error', 'Report not found.');

            return;
        }

        $nextMonth = \Carbon\Carbon::parse($source->report_date)->addMonth()->format('m-Y');

        $result = $service->cloneSummary($id, $nextMonth);

        if ($result['success']) {
            session()->flash('success', $result['message']);

            return;
        }

        session()->flash('error', $result['message']);
    }

    public function render()
    {
        $user = auth()->user();

        $query = Report::query()
            ->with(['user'])
            ->withTotals()
            ->withPrevTotals();

        // Role based visibility
        if ($user->hasRole('DIRECTOR')) {
            $query->whereHas('approvalRequest', fn($q) => $q->whereIn('status', ['IN_REVIEW', 'APPROVED', 'REJECTED']));
        } elseif ($user->is_head && $user->hasRole('DESIGN')) {
            $query->whereHas('approvalRequest', fn($q) => $q->where('status', 'IN_REVIEW'));
        } elseif ($user->is_gm) {
            $query->whereHas('approvalRequest', fn($q) => $q->where('status', 'IN_REVIEW'));
        }

        // Keyword
        if ($this->search) {
            $like = '%' . trim($this->search) . '%';
            $query->where(function (Builder $q) use ($like) {
                $q->where('doc_num', 'like', $like)->orWhereHas(
                    'user',
                    fn (Builder $uq) => $uq->where('name', 'like', $like),
                );
            });
        }

        // Status filter
        if ($this->status !== null && $this->status !== '') {
            if ($this->status === 'DRAFT') {
                $query->whereDoesntHave('approvalRequest');
            } else {
                $query->whereHas('approvalRequest', fn($q) => $q->where('status', $this->status));
            }
        }

        // Month range (report_date stores the first day of month)
        // Expecting mm-yyyy. Convert to YYYY-mm-01 boundaries.
        $from = $this->normalizeMonth($this->monthFrom); // 'YYYY-mm-01' or null
        $to = $this->normalizeMonth($this->monthTo);

        if ($from && $to) {
            $query->whereBetween('report_date', [$from, $to]);
        } elseif ($from) {
            $query->where('report_date', '>=', $from);
        } elseif ($to) {
            $query->where('report_date', '<=', $to);
        }

        // Sorting (secure via whitelist)
        $column = $this->sortable[$this->sortField] ?? 'created_at';
        $dir = in_array(strtolower($this->sortDirection), ['asc', 'desc'])
            ? $this->sortDirection
            : 'desc';
        $query->orderBy($column, $dir)->orderBy('id', 'desc');

        $reports = $query->paginate($this->perPage);

        return view('livewire.monthly-budget-summary.index', [
            'reports' => $reports,
            'authUser' => $user,
            'showGenerateButton' => $this->showGenerateButton,
        ]);
    }
}
