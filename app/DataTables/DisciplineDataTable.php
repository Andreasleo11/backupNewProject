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
     * Set which evaluation type this DataTable instance represents.
     */
    public function forType(string $type): static
    {
        $this->type = $type;

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
                return match (true) {
                    $row->total >= 91 => 'A',
                    $row->total >= 71 => 'B',
                    $row->total >= 61 => 'C',
                    default           => 'D',
                };
            })
            ->addColumn('action', function (EvaluationData $row) use ($type) {
                $disabled  = $row->is_lock ? 'disabled' : '';
                $modalId   = '#edit-discipline-modal';
                $updateUrl = route($this->updateRoute(), ['id' => $row->id]);

                return '<button class="btn btn-primary edit-discipline-btn"
                    data-bs-toggle="modal"
                    data-bs-target="' . $modalId . '"
                    data-id="' . $row->id . '"
                    data-update-url="' . $updateUrl . '"
                    ' . $disabled . '>
                    <i class="bx bx-edit"></i>
                </button>';
            })
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
                        if (empty($row->depthead)) {
                            return ''; // Pending — no highlight
                        }
                        if ($row->depthead === 'rejected') {
                            return 'table-danger'; // Rejected
                        }
                        if (! empty($row->generalmanager)) {
                            return 'table-primary'; // Fully approved
                        }
                        return 'table-success'; // DeptHead approved, awaiting GM/HRD
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

        $employees = match ($this->type) {
            'yayasan' => $resolver->resolveYayasanForUser($user),
            'magang'  => $resolver->resolveMagangForUser($user),
            default   => $resolver->resolveForUser($user),
        };

        /** @var \Illuminate\Database\Eloquent\Builder $query */
        $query = EvaluationData::with('karyawan')
            ->whereIn('NIK', $employees->pluck('NIK'));

        return $query;
    }

    /**
     * Optional HTML builder configuration.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId($this->tableId())
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
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
        return match ($this->type) {
            'yayasan' => 'discipline.yayasan.update',
            'magang'  => 'discipline.magang.update',
            default   => 'editdiscipline',
        };
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
                ->addClass('align-middle text-center')
                ->orderable(false),
            Column::make('Department')
                ->data('karyawan.dept_code')
                ->searchable(false)
                ->addClass('align-middle text-center')
                ->orderable(false),
            Column::make('start_date')
                ->title('Start Date')
                ->data('karyawan.start_date')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle text-center')
                ->orderable(false),
            Column::make('status')
                ->title('Status')
                ->data('karyawan.employment_scheme')
                ->exportable(false)
                ->searchable(false)
                ->addClass('align-middle text-center')
                ->orderable(false),
            Column::make('Month')->addClass('align-middle text-center'),
            Column::make('Alpha')->exportable(false)->addClass('align-middle text-center'),
            Column::make('Telat')->exportable(false)->addClass('align-middle text-center'),
            Column::make('Izin')->exportable(false)->addClass('align-middle text-center'),
            Column::make('Sakit')->exportable(false)->addClass('align-middle text-center'),
            Column::make('totalkehadiran')
                ->title('Total Nilai Kehadiran')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle text-center text-bg-secondary')
                ->orderable(false),
            Column::make('kerajinan_kerja')->addClass('align-middle text-center')->title('Kinerja Kerja'),
            Column::make('kerapian_kerja')->addClass('align-middle text-center')->title('Kerapian'),
            Column::make('loyalitas')->addClass('align-middle text-center'),
            Column::make('perilaku_kerja')->addClass('align-middle text-center')->title('Etika dan Kesopanan'),
            Column::make('prestasi')->addClass('align-middle text-center'),
            Column::make('totaldiscipline')
                ->title('Total Nilai Kedisiplinan')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle text-center text-bg-secondary')
                ->orderable(false),
            Column::make('total')->exportable(false)->addClass('align-middle text-center'),
            Column::make('grade')
                ->title('Grade')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle text-center')
                ->orderable(false),
            Column::computed('action')
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
            Column::make('NIK'),
            Column::make('Name')
                ->data('karyawan.Nama')
                ->searchable(false)
                ->addClass('align-middle')
                ->orderable(false),
            Column::make('dept')->addClass('align-middle'),
            Column::make('start_date')
                ->title('Start Date')
                ->data('karyawan.start_date')
                ->searchable(false)
                ->addClass('align-middle')
                ->orderable(false),
            Column::make('status')
                ->title('Status')
                ->data('karyawan.status')
                ->searchable(false)
                ->addClass('align-middle')
                ->orderable(false),
            Column::make('Month'),
            Column::make('Alpha'),
            Column::make('Telat'),
            Column::make('Izin'),
            Column::make('Sakit'),
            Column::make('kemampuan_kerja'),
            Column::make('kecerdasan_kerja'),
            Column::make('qualitas_kerja')->title('Kualitas Kerja'),
            Column::make('disiplin_kerja'),
            Column::make('kepatuhan_kerja'),
            Column::make('lembur'),
            Column::make('efektifitas_kerja'),
            Column::make('relawan')->title('Ringan Tangan'),
            Column::make('integritas'),
            Column::make('totaldiscipline')
                ->title('Total Nilai Kedisiplinan')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle')
                ->orderable(false),
            Column::make('total')->exportable(false),
            Column::make('grade')
                ->title('Grade')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle')
                ->orderable(false),
            Column::make('pengawas')
                ->title('Approved By')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle')
                ->orderable(false),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center align-middle'),
            Column::make('remark')
                ->title('Remark Reject')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle')
                ->orderable(false),
        ];
    }
}
