<?php

namespace App\Livewire;

use App\Models\InspectionForm\InspectionReport;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class InspectionIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // ── URL-bound params (keep your behavior) ───────────────────────────
    #[Url(as: 's', except: '')]
    public string $search = '';

    #[Url(as: 'sort', except: 'inspection_date')]
    public string $sortField = 'inspection_date';

    #[Url(as: 'dir', except: 'desc')]
    public string $sortDir = 'desc';

    #[Url(as: 'perPage', except: 10)]
    public int $perPage = 10;

    public array $showCol = [
        'document_number' => true,
        'inspection_date' => true,
        'shift' => true,
        'customer' => true,
        'part_number' => true,
    ];

    public array $filters = [
        'document_number' => '',
        'date_from' => '',
        'date_to' => '',
        'shift' => '',
        'customer' => '',
        'part_number' => '',
    ];

    public bool $ready = false;

    private const SORTABLE = [
        'inspection_date',
        'document_number',
        'shift',
        'customer',
        'part_number',
    ];

    public function mount()
    {
        $this->ready = false;
    }

    public function load()
    {
        $this->ready = true;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function updatedFilters($value, $name): void
    {
        if (in_array($name, ['date_from', 'date_to'], true)) {
            $from = $this->filters['date_from'] ?: null;
            $to = $this->filters['date_to'] ?: null;
            if ($from && $to && $from > $to) {
                [$this->filters['date_from'], $this->filters['date_to']] = [$to, $from]; // swap
            }
        }
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDir = 'asc';
        }
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->filters = [
            'document_number' => '',
            'date_from' => '',
            'date_to' => '',
            'shift' => '',
            'customer' => '',
            'part_number' => '',
        ];
        $this->resetPage();
    }

    private function baseQuery()
    {
        $field = in_array($this->sortField, self::SORTABLE, true)
            ? $this->sortField
            : 'inspection_date';
        $dir = $this->sortDir === 'asc' ? 'asc' : 'desc';

        return InspectionReport::query()
            ->select([
                'id',
                'document_number',
                'inspection_date',
                'shift',
                'customer',
                'part_number',
            ])
            // global search
            ->when($this->search !== '', function ($q) {
                $s = '%'.trim($this->search).'%';
                $q->where(
                    fn ($qq) => $qq
                        ->where('document_number', 'like', $s)
                        ->orWhere('customer', 'like', $s)
                        ->orWhere('part_number', 'like', $s),
                );
            })
            // column filters
            ->when(
                $this->filters['document_number'] !== '',
                fn ($q) => $q->where(
                    'document_number',
                    'like',
                    '%'.trim($this->filters['document_number']).'%',
                ),
            )
            ->when(
                $this->filters['customer'] !== '',
                fn ($q) => $q->where(
                    'customer',
                    'like',
                    '%'.trim($this->filters['customer']).'%',
                ),
            )
            ->when(
                $this->filters['part_number'] !== '',
                fn ($q) => $q->where(
                    'part_number',
                    'like',
                    '%'.trim($this->filters['part_number']).'%',
                ),
            )
            ->when(
                $this->filters['shift'] !== '',
                fn ($q) => $q->where('shift', $this->filters['shift']),
            )
            ->when(
                $this->filters['date_from'] !== '',
                fn ($q) => $q->whereDate('inspection_date', '>=', $this->filters['date_from']),
            )
            ->when(
                $this->filters['date_to'] !== '',
                fn ($q) => $q->whereDate('inspection_date', '<=', $this->filters['date_to']),
            )
            // sort + stable tiebreak
            ->orderBy($field, $dir)
            ->orderBy('id', 'desc');
    }

    public function exportCsv()
    {
        // If initial defer is still on, flip it so export uses real data
        if (! $this->ready) {
            $this->ready = true;
        }

        $filename = 'inspection-reports-'.now()->format('Ymd-His').'.csv';

        // Respect visible columns
        $labels = [
            'document_number' => 'Document No',
            'inspection_date' => 'Date',
            'shift' => 'Shift',
            'customer' => 'Customer',
            'part_number' => 'Part Number',
        ];
        $visibleCols = array_keys(array_filter($this->showCol)); // keep order of showCol
        $headers = array_map(fn ($k) => $labels[$k] ?? $k, $visibleCols);

        return response()->streamDownload(
            function () use ($visibleCols, $headers) {
                $out = fopen('php://output', 'w');

                // BOM so Excel opens UTF-8 correctly
                fwrite($out, "\xEF\xBB\xBF");

                // Header row
                fputcsv($out, $headers);

                // Stream rows in chunks to avoid memory spikes
                $this->baseQuery()->chunk(1000, function ($chunk) use ($out, $visibleCols) {
                    foreach ($chunk as $r) {
                        $row = [];
                        foreach ($visibleCols as $col) {
                            $val = $r->{$col};
                            if ($col === 'inspection_date' && $val) {
                                $val = \Illuminate\Support\Carbon::parse($val)->format('Y-m-d');
                            }
                            $row[] = $val;
                        }
                        fputcsv($out, $row);
                    }
                });

                fclose($out);
            },
            $filename,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
                'Pragma' => 'no-cache',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ],
        );
    }

    public function render()
    {
        $shiftOptions = InspectionReport::query()
            ->select('shift')
            ->distinct()
            ->orderBy('shift')
            ->pluck('shift')
            ->all();
        $perPageOptions = [10, 25, 50, 100];

        $reports = $this->ready
            ? $this->baseQuery()->paginate($this->perPage)->withQueryString()
            : collect();

        return view(
            'livewire.inspection-form.index',
            compact('reports', 'shiftOptions', 'perPageOptions'),
        )->layout('layouts.guest');
    }
}
