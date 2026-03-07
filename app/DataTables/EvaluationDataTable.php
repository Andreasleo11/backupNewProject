<?php

namespace App\DataTables;

use App\Domain\Evaluation\Services\DepartmentEmployeeResolver;
use App\Domain\Evaluation\Services\EvaluationScoreCalculatorService;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Models\EvaluationData;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

/**
 * EvaluationDataTable
 *
 * A single, configurable DataTable for all three discipline evaluation types:
 * 'regular', 'yayasan', and 'magang'.
 *
 * Usage:
 *   $dataTable = app(EvaluationDataTable::class)->forType('yayasan');
 *   return $dataTable->render('setting.disciplineyayasanindex', compact(...));
 *
 * What changes per type:
 *   - query()      → which employees are visible (via DepartmentEmployeeResolver)
 *   - getColumns() → regular uses old 5-field scoring; yayasan/magang use new 9-field
 *   - action button → points to the correct modal and update route
 *   - row colours  → approval status row highlighting (yayasan/magang only)
 *   - table HTML id → unique per type so JS doesn't conflict
 */
class EvaluationDataTable extends DataTable
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
        $calculator = app(EvaluationScoreCalculatorService::class);
        $canGrade   = Auth::user()?->can('evaluation.grade') ?? false;
        $type       = $this->type;

        $dt = (new EloquentDataTable($query))
            ->addColumn('grade', function (Employee $row) {
                $evalData = $row->evaluationData->first();
                if (! $evalData) {
                    return '<span class="px-2.5 py-1 rounded-md text-xs font-bold border bg-slate-100 text-slate-500 border-slate-200">Pending</span>';
                }

                $grade = match (true) {
                    $evalData->total >= 91 => 'A',
                    $evalData->total >= 71 => 'B',
                    $evalData->total >= 61 => 'C',
                    default                => 'D',
                };
                
                $color = match ($grade) {
                    'A' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    'B' => 'bg-sky-100 text-sky-800 border-sky-200',
                    'C' => 'bg-amber-100 text-amber-800 border-amber-200',
                    default => 'bg-rose-100 text-rose-800 border-rose-200',
                };

                return '<span class="px-2.5 py-1 rounded-md text-xs font-bold border ' . $color . '">' . $grade . '</span>';
            })
            ->addColumn('action', function (Employee $row) use ($type, $canGrade) {
                if (! $canGrade) {
                    return '<span class="text-xs text-slate-400 italic">No Access</span>';
                }

                $evalData = $row->evaluationData->first();
                $status   = $evalData?->approval_status;

                // Derive lock from approval_status — no is_lock column needed
                $isLocked = in_array($status, ['dept_approved', 'fully_approved']);

                if ($isLocked) {
                    return '<button disabled class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-3 py-1.5 text-xs font-semibold text-slate-400 cursor-not-allowed" title="Locked: already approved">
                        <i class="bx bx-lock-alt"></i> Locked
                    </button>';
                }

                $updateUrl = route('evaluation.grade.nik', [
                    'nik'   => $row->nik,
                    'month' => $this->filterMonth ?: date('n'),
                    'year'  => $this->filterYear  ?: date('Y'),
                ]);

                $fetchUrl = route('evaluation.show.nik', [
                    'nik'   => $row->nik,
                    'month' => $this->filterMonth ?: date('n'),
                    'year'  => $this->filterYear  ?: date('Y'),
                ]);

                [$icon, $label, $style] = $evalData
                    ? ['bx-edit-alt', 'Edit',   'text-indigo-600 border-indigo-200 bg-indigo-50 hover:bg-indigo-100']
                    : ['bx-plus-circle', 'Grade', 'text-emerald-600 border-emerald-200 bg-emerald-50 hover:bg-emerald-100'];

                $onclick = "window.dispatchEvent(new CustomEvent('open-evaluate-modal', { detail: { fetchUrl: '{$fetchUrl}', updateUrl: '{$updateUrl}' } }))";

                return "<button onclick=\"{$onclick}\" class=\"inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-semibold transition-colors {$style}\">
                    <i class=\"bx {$icon}\"></i> {$label}
                </button>";
            })
            ->addColumn('approval_status', function (Employee $row) {
                $evalData = $row->evaluationData->first();
                $status   = $evalData?->approval_status ?? 'pending';

                [$label, $classes] = match ($status) {
                    'graded'         => ['Graded',       'bg-amber-100 text-amber-800 border-amber-200'],
                    'dept_approved'  => ['Dept Approved','bg-sky-100 text-sky-800 border-sky-200'],
                    'fully_approved' => ['Final',        'bg-emerald-100 text-emerald-800 border-emerald-200'],
                    'rejected'       => ['Rejected',     'bg-rose-100 text-rose-800 border-rose-200'],
                    default          => ['Pending',      'bg-slate-100 text-slate-500 border-slate-200'],
                };

                return '<span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border ' . $classes . '">' . $label . '</span>';
            })
            ->addColumn('absence_summary', function (Employee $row) {
                $evalData = $row->evaluationData->first();
                if (! $evalData) {
                    return '<span class="text-slate-400 italic text-xs">Belum dinilai</span>';
                }

                $badges = [];
                if ($evalData->Alpha > 0) $badges[] = '<span class="px-1.5 py-0.5 rounded bg-rose-100 text-rose-700 font-bold border border-rose-200" title="Alpha">A: ' . $evalData->Alpha . '</span>';
                if ($evalData->Telat > 0) $badges[] = '<span class="px-1.5 py-0.5 rounded bg-amber-100 text-amber-700 font-bold border border-amber-200" title="Telat">T: ' . $evalData->Telat . '</span>';
                if ($evalData->Izin > 0)  $badges[] = '<span class="px-1.5 py-0.5 rounded bg-sky-100 text-sky-700 font-bold border border-sky-200" title="Izin">I: ' . $evalData->Izin . '</span>';
                if ($evalData->Sakit > 0) $badges[] = '<span class="px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-700 font-bold border border-indigo-200" title="Sakit">S: ' . $evalData->Sakit . '</span>';

                if (empty($badges)) {
                    return '<span class="text-emerald-600 font-semibold text-xs"><i class="bx bx-check-circle"></i> Sempurna</span>';
                }
                
                return '<div class="flex flex-wrap gap-1 items-center justify-center text-xs">' . implode('', $badges) . '</div>';
            })
            ->addColumn('total', function (Employee $row) {
                $evalData = $row->evaluationData->first();
                return $evalData ? $evalData->total : 0;
            })
            ->rawColumns(['grade', 'approval_status', 'absence_summary', 'action'])
            ->setRowId('nik')
            ->orderColumn('total', function ($query, $order) {
                // Server-side sort for the computed 'total' column via a correlated sub-query.
                $month = $this->filterMonth;
                $year  = $this->filterYear;

                $sub = EvaluationData::select('total')
                    ->whereColumn('nik', 'employees.nik')
                    ->when($month, fn ($q) => $q->whereMonth('Month', $month))
                    ->when($year,  fn ($q) => $q->whereYear('Month', $year))
                    ->limit(1);

                $query->orderBy($sub, $order);
            })
            ->filterColumn('total', function ($query, $keyword) {
                // Allow filtering/searching by numeric total value.
                $month = $this->filterMonth;
                $year  = $this->filterYear;

                $query->whereHas('evaluationData', function ($q) use ($keyword, $month, $year) {
                    $q->when($month, fn ($q) => $q->whereMonth('Month', $month))
                      ->when($year,  fn ($q) => $q->whereYear('Month', $year))
                      ->where('total', 'like', "%{$keyword}%");
                });
            });

        // Regular: add old-system computed columns (split attendance + criteria)
        if ($type === 'regular') {
            $dt->addColumn('totalkehadiran', function (Employee $row) {
                    $evalData = $row->evaluationData->first();
                    if (! $evalData) return 0;
                    
                    $deduction = ($evalData->Alpha * 10) + ($evalData->Izin * 2) + $evalData->Sakit + ($evalData->Telat * 0.5);
                    return max(0, 40 - $deduction);
                })
                ->addColumn('totaldiscipline', function (Employee $row) use ($calculator) {
                    $evalData = $row->evaluationData->first();
                    if (! $evalData) return 0;

                    $scores = [
                        'kerajinan_kerja' => $evalData->kerajinan_kerja,
                        'kerapian_kerja'  => $evalData->kerapian_kerja,
                        'prestasi'        => $evalData->prestasi,
                        'loyalitas'       => $evalData->loyalitas,
                        'perilaku_kerja'  => $evalData->perilaku_kerja,
                    ];
                    return $calculator->calculateTotalOld($scores, $evalData) - 40 + (
                        ($evalData->Alpha * 10) + ($evalData->Izin * 2) + $evalData->Sakit + ($evalData->Telat * 0.5)
                    );
                });
        }

        // Yayasan / Magang: new 9-field system + approval row colouring
        if (in_array($type, ['yayasan', 'magang'], true)) {
            $dt->addColumn('totaldiscipline', function (Employee $row) use ($calculator) {
                    $evalData = $row->evaluationData->first();
                    if (! $evalData) return 0;

                    $scores = [
                        'kemampuan_kerja'   => $evalData->kemampuan_kerja,
                        'kecerdasan_kerja'  => $evalData->kecerdasan_kerja,
                        'qualitas_kerja'    => $evalData->qualitas_kerja,
                        'disiplin_kerja'    => $evalData->disiplin_kerja,
                        'kepatuhan_kerja'   => $evalData->kepatuhan_kerja,
                        'lembur'            => $evalData->lembur,
                        'efektifitas_kerja' => $evalData->efektifitas_kerja,
                        'relawan'           => $evalData->relawan,
                        'integritas'        => $evalData->integritas,
                    ];
                    return $calculator->calculateTotal($scores, $evalData);
                })
                ->addColumn('pengawas', function (Employee $row) {
                    $evalData = $row->evaluationData->first();
                    return $evalData ? $evalData->pengawas : null;
                })
                ->setRowAttr([
                    'class' => function (Employee $row) {
                        $evalData = $row->evaluationData->first();
                        if (! $evalData) return 'bg-slate-50/50';

                        return match ($evalData->approval_status) {
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
     * Get query source — scopes to Employee master record, then correctly scopes
     * the EvaluationData eager-load relationship so it only retrieves data FOR
     * the selected month/year.
     */
    public function query(Employee $model): QueryBuilder
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
            $employees = collect();
        }

        // We filter out deleted/invalid employees and grab only the NIKs
        $niks = $employees->pluck('nik')->filter()->values();

        // 1. Base query from Employee Model
        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = $model->newQuery()->whereIn('nik', $niks);

        // 2. Eager load the nested EvaluationData constraint for this EXACT month/year
        $query->with(['evaluationData' => function ($q) {
            if ($this->filterMonth) {
                $q->whereMonth('Month', $this->filterMonth);
            }
            if ($this->filterYear) {
                $q->whereYear('Month', $this->filterYear);
            }
        }]);

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId($this->tableId())
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'asc')
            ->buttons([]); // Export handled via the action bar button
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
            Column::make('nik')->title('NIK')->addClass('align-middle text-center'),
            Column::make('name')
                ->title('Name')
                ->addClass('align-middle font-semibold text-slate-800'),
            Column::make('dept_code')
                ->title('Department')
                ->addClass('align-middle text-center'),
            Column::make('employment_scheme')
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
            Column::computed('approval_status')
                ->title('Status')
                ->exportable(false)
                ->searchable(false)
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
            Column::make('nik')->title('NIK')->addClass('align-middle text-center'),
            Column::make('name')
                ->title('Name')
                ->addClass('align-middle font-semibold text-slate-800'),
            Column::make('dept_code')
                ->title('Department')
                ->addClass('align-middle text-center'),
            Column::make('employment_scheme')
                ->title('Status')
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
            Column::make('pengawas')
                ->title('Graded By')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle text-center text-sm font-medium text-slate-600')
                ->orderable(false),
            Column::computed('approval_status')
                ->title('Status')
                ->exportable(false)
                ->searchable(false)
                ->addClass('align-middle text-center')
                ->orderable(false),
            Column::computed('action')
                ->title('Aksi')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center align-middle border-l border-slate-100'),
        ];
    }
}
