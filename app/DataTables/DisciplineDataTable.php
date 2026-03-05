<?php

namespace App\DataTables;

use App\Domain\Discipline\Services\DepartmentEmployeeResolver;
use App\Domain\Discipline\Services\DisciplineScoreCalculatorService;
use App\Models\EvaluationData;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

/**
 * DisciplineDataTable
 *
 * A single, configurable DataTable for all three discipline evaluation types:
 * 'regular', 'yayasan', and 'magang'.
 *
 * Usage:
 *   $dataTable = app(DisciplineDataTable::class)->forType('yayasan');
 *   return $dataTable->render('setting.disciplineyayasanindex', compact(...));
 *
 * What changes per type:
 *   - query()      → which employees are visible (via DepartmentEmployeeResolver)
 *   - getColumns() → regular uses old 5-field scoring; yayasan/magang use new 9-field
 *   - action button → points to the correct modal and update route
 *   - row colours  → approval status row highlighting (yayasan/magang only)
 *   - table HTML id → unique per type so JS doesn't conflict
 */
class DisciplineDataTable extends DataTable
{
    /**
     * Evaluation type: 'regular' | 'yayasan' | 'magang'
     */
    protected string $type = 'regular';

    /**
     * Optional period filter (month + year).
     * When set, the query is scoped to this specific month+year.
     */
    protected ?int $filterMonth = null;
    protected ?int $filterYear  = null;

    /**
     * Set which evaluation type this DataTable instance represents.
     */
    public function forType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Scope the DataTable query to a specific evaluation period.
     */
    public function forPeriod(int $month, int $year): static
    {
        $this->filterMonth = $month;
        $this->filterYear  = $year;

        return $this;
    }

    /**
     * Build the DataTable with computed columns.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $calculator = app(DisciplineScoreCalculatorService::class);
        $type       = $this->type;

        $dt = (new EloquentDataTable($query))
            ->addColumn('grade', function (EvaluationData $row) {
                // Determine grade
                $grade = match (true) {
                    $row->total >= 91 => 'A',
                    $row->total >= 71 => 'B',
                    $row->total >= 61 => 'C',
                    default           => 'D',
                };
                
                // Map to badge colour
                $color = match ($grade) {
                    'A' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    'B' => 'bg-sky-100 text-sky-800 border-sky-200',
                    'C' => 'bg-amber-100 text-amber-800 border-amber-200',
                    default => 'bg-rose-100 text-rose-800 border-rose-200',
                };

                return '<span class="px-2.5 py-1 rounded-md text-xs font-bold border ' . $color . '">' . $grade . '</span>';
            })
            ->addColumn('action', function (EvaluationData $row) use ($type) {
                $disabled  = $row->is_lock ? 'disabled' : '';
                $modalId   = '#edit-discipline-modal';
                $updateUrl = route('evaluation.grade', ['id' => $row->id]);

                // Modern premium action button (Alpine-friendly dispatch)
                return '<button class="btn btn-sm btn-light border-slate-200 text-indigo-600 shadow-sm hover:bg-indigo-50 hover:border-indigo-200 transition-all rounded-lg"
                    onclick="window.dispatchEvent(new CustomEvent(\'open-evaluate-modal\', { detail: { id: ' . $row->id . ', url: \'' . $updateUrl . '\' } }))"
                    title="Beri Nilai"
                    ' . $disabled . '>
                    <i class="bx bx-edit-alt"></i>
                </button>';
            })
            ->addColumn('absence_summary', function (EvaluationData $row) {
                $badges = [];
                if ($row->Alpha > 0) $badges[] = '<span class="px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-bold border border-rose-200" title="Alpha">A: ' . $row->Alpha . '</span>';
                if ($row->Telat > 0) $badges[] = '<span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-bold border border-amber-200" title="Telat">T: ' . $row->Telat . '</span>';
                if ($row->Izin > 0)  $badges[] = '<span class="px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-bold border border-sky-200" title="Izin">I: ' . $row->Izin . '</span>';
                if ($row->Sakit > 0) $badges[] = '<span class="px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 font-bold border border-indigo-200" title="Sakit">S: ' . $row->Sakit . '</span>';

                if (empty($badges)) {
                    return '<span class="text-emerald-600 font-semibold text-xs"><i class="bx bx-check-circle"></i> Sempurna</span>';
                }
                
                return '<div class="flex flex-wrap gap-1 items-center justify-center text-xs">' . implode('', $badges) . '</div>';
            })
            ->rawColumns(['grade', 'absence_summary', 'action'])
            ->setRowId('id');

        // Regular: add old-system computed columns (split attendance + criteria)
        if ($type === 'regular') {
            $dt->addColumn('totalkehadiran', function (EvaluationData $row) {
                    // Attendance score = 40 − (Alpha×10 + Izin×2 + Sakit + Telat×0.5)
                    $deduction = ($row->Alpha * 10) + ($row->Izin * 2) + $row->Sakit + ($row->Telat * 0.5);
                    return max(0, 40 - $deduction);
                })
                ->addColumn('totaldiscipline', function (EvaluationData $row) use ($calculator) {
                    $scores = [
                        'kerajinan_kerja' => $row->kerajinan_kerja,
                        'kerapian_kerja'  => $row->kerapian_kerja,
                        'prestasi'        => $row->prestasi,
                        'loyalitas'       => $row->loyalitas,
                        'perilaku_kerja'  => $row->perilaku_kerja,
                    ];
                    // Return old-system criteria score only (strip base-40 + add back penalty)
                    return $calculator->calculateTotalOld($scores, $row) - 40 + (
                        ($row->Alpha * 10) + ($row->Izin * 2) + $row->Sakit + ($row->Telat * 0.5)
                    );
                });
        }

        // Yayasan / Magang: new 9-field system + approval row colouring
        if (in_array($type, ['yayasan', 'magang'], true)) {
            $dt->addColumn('totaldiscipline', function (EvaluationData $row) use ($calculator) {
                    $scores = [
                        'kemampuan_kerja'   => $row->kemampuan_kerja,
                        'kecerdasan_kerja'  => $row->kecerdasan_kerja,
                        'qualitas_kerja'    => $row->qualitas_kerja,
                        'disiplin_kerja'    => $row->disiplin_kerja,
                        'kepatuhan_kerja'   => $row->kepatuhan_kerja,
                        'lembur'            => $row->lembur,
                        'efektifitas_kerja' => $row->efektifitas_kerja,
                        'relawan'           => $row->relawan,
                        'integritas'        => $row->integritas,
                    ];
                    return $calculator->calculateTotal($scores, $row);
                })
                ->setRowAttr([
                    'class' => function (EvaluationData $row) {
                        return match ($row->approval_status) {
                            'rejected'       => 'table-danger',
                            'fully_approved' => 'table-primary',
                            'dept_approved'  => 'table-success',
                            default          => '',
                        };
                    },
                ]);
        }

        return $dt;
    }

    /**
     * Get query source — delegates to DepartmentEmployeeResolver so access
     * rules live in the Domain layer, not here.
     */
    public function query(EvaluationData $model): QueryBuilder
    {
        $user     = Auth::user();
        $resolver = app(DepartmentEmployeeResolver::class);

        try {
            $employees = match ($this->type) {
                'yayasan' => $resolver->resolveYayasanForUser($user),
                'magang'  => $resolver->resolveMagangForUser($user),
                default   => $resolver->resolveForUser($user),
            };
        } catch (\Throwable) {
            // User has no visible employees for this type
            // (e.g. not a dept head, or department not in config).
            // Return an empty result set so the DataTable renders empty instead of 500.
            $employees = collect();
        }

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = EvaluationData::with('karyawan')
            ->whereIn('NIK', $employees->pluck('NIK'))
            ->where('evaluation_type', $this->type);

        // Apply period filter when set (from EvaluationController)
        if ($this->filterMonth) {
            $query->whereMonth('Month', $this->filterMonth);
        }
        if ($this->filterYear) {
            $query->whereYear('Month', $this->filterYear);
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId($this->tableId())
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1, 'asc') // Sort by NIK default, although standard is order(1).
            // Default sort: Total (which is column index index 5) Ascending
            // So employees with 0 score (Pending) appear first.
            // Note: DataTables order is 0-indexed. 
            // id=0, NIK=1, Name=2, Dept=3, Status=4, Total=5
            ->orderBy(5, 'asc') 
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload'),
            ]);
    }

    /**
     * Get columns based on type.
     * Regular uses old 5-field scoring; Yayasan/Magang use new 9-field.
     */
    public function getColumns(): array
    {
        return $this->type === 'regular'
            ? $this->regularColumns()
            : $this->newSystemColumns();
    }

    /**
     * Export filename.
     */
    protected function filename(): string
    {
        return 'Discipline_' . ucfirst($this->type) . '_' . date('YmdHis');
    }

    /**
     * Return column definitions as a plain array suitable for JSON serialization.
     * Used by the evaluation/index.blade.php view to pass column defs to DataTables JS.
     */
    public static function columnsForJs(string $type): array
    {
        $instance = (new static())->forType($type);

        return array_map(function (Column $col) {
            return [
                'data'       => $col->data,
                'name'       => $col->name,
                'title'      => $col->title,
                'orderable'  => $col->orderable ?? true,
                'searchable' => $col->searchable ?? true,
                'visible'    => $col->visible ?? true,
                'className'  => $col->className ?? '',
            ];
        }, $instance->getColumns());
    }

    // ──────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────

    /**
     * Unique HTML table ID per type to prevent JS conflicts.
     */
    private function tableId(): string
    {
        return match ($this->type) {
            'yayasan' => 'disciplineyayasantable-table',
            'magang'  => 'disciplinemagang-table',
            default   => 'disciplinetable-table',
        };
    }

    /**
     * The update route name for this type.
     * Used to build the data-update-url on the action button.
     */
    private function updateRoute(): string
    {
        return 'evaluation.grade';
    }

    /**
     * Columns for Regular employees (old 5-field scoring system, attendance split).
     */
    private function regularColumns(): array
    {
        return [
            Column::make('id')->visible(false),
            Column::make('NIK')->title('NIK')->addClass('align-middle text-center'),
            Column::make('Name')
                ->data('karyawan.name')
                ->searchable(false)
                ->addClass('align-middle font-semibold text-slate-800')
                ->orderable(false),
            Column::make('Department')
                ->data('karyawan.dept_code')
                ->searchable(false)
                ->addClass('align-middle text-center')
                ->orderable(false),
            Column::make('status')
                ->title('Status')
                ->data('karyawan.employment_scheme')
                ->exportable(false)
                ->searchable(false)
                ->addClass('align-middle text-center')
                ->orderable(false),
            Column::computed('absence_summary')
                ->title('Kehadiran')
                ->exportable(false)
                ->searchable(false)
                ->addClass('align-middle text-center'),
            Column::make('total')
                ->title('Total Nilai')
                ->exportable(false)
                ->addClass('align-middle text-center font-bold text-indigo-600'),
            Column::make('grade')
                ->title('Grade')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle text-center')
                ->orderable(false),
            Column::computed('action')
                ->title('Aksi')
                ->exportable(false)
                ->printable(false)
                ->addClass('align-middle text-center'),
        ];
    }

    /**
     * Columns for Yayasan/Magang employees (new 9-field scoring system).
     * Includes approval status columns (pengawas, remark).
     */
    private function newSystemColumns(): array
    {
        return [
            Column::make('id')->visible(false)->exportable(true),
            Column::make('NIK')->addClass('align-middle text-center'),
            Column::make('Name')
                ->data('karyawan.name')
                ->searchable(false)
                ->addClass('align-middle font-semibold text-slate-800')
                ->orderable(false),
            Column::make('dept')->title('Department')->data('karyawan.dept_code')->addClass('align-middle text-center'),
            Column::make('status')
                ->title('Status')
                ->data('karyawan.employment_scheme')
                ->searchable(false)
                ->addClass('align-middle text-center')
                ->orderable(false),
            Column::computed('absence_summary')
                ->title('Kehadiran')
                ->exportable(false)
                ->searchable(false)
                ->addClass('align-middle text-center'),
            Column::make('total')
                ->title('Total Nilai')
                ->exportable(false)
                ->addClass('align-middle text-center font-bold text-indigo-600'),
            Column::make('grade')
                ->title('Grade')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle text-center border-x border-slate-100 bg-slate-50/50')
                ->orderable(false),
            Column::make('pengawas')
                ->title('Approved By')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle text-center text-sm font-medium text-slate-600')
                ->orderable(false),
            Column::computed('action')
                ->title('Aksi')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center align-middle border-l border-slate-100'),
        ];
    }
}
