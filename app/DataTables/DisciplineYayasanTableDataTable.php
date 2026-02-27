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

class DisciplineYayasanTableDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $calculator = app(DisciplineScoreCalculatorService::class);

        return (new EloquentDataTable($query))
            ->addColumn('totaldiscipline', function (EvaluationData $row) use ($calculator) {
                // New scoring system: 9 criteria (A–E), penalties subtracted
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
            ->addColumn('grade', function (EvaluationData $row) {
                return match (true) {
                    $row->total >= 91 => 'A',
                    $row->total >= 71 => 'B',
                    $row->total >= 61 => 'C',
                    default           => 'D',
                };
            })
            ->addColumn('action', function (EvaluationData $row) {
                $disabled = $row->is_lock ? 'disabled' : '';

                return '<button class="btn btn-primary edit-button"
                    data-bs-toggle="modal"
                    data-bs-target="#edit-discipline-yayasan-modal"
                    data-id="' . $row->id . '" '
                    . $disabled . '>
                    <i class="bx bx-edit"></i>
                </button>';
            })
            ->setRowId('id')
            ->setRowAttr([
                'class' => function (EvaluationData $row) {
                    if (empty($row->depthead)) {
                        return ''; // Pending — no style
                    }

                    if ($row->depthead === 'rejected') {
                        return 'table-danger'; // Rejected
                    }

                    if (! empty($row->generalmanager)) {
                        return 'table-primary'; // Fully approved (GM/HRD signed off)
                    }

                    return 'table-success'; // Dept head approved, awaiting GM
                },
            ]);
    }

    /**
     * Get query source of dataTable.
     *
     * Delegates to DepartmentEmployeeResolver so that the Yayasan
     * employee access rules live in ONE place (Domain layer), not here.
     */
    public function query(EvaluationData $model): QueryBuilder
    {
        $user     = Auth::user();
        $resolver = app(DepartmentEmployeeResolver::class);

        // resolveYayasanForUser() handles GM access, email-based access,
        // and department-scoped access for dept heads
        $employees   = $resolver->resolveYayasanForUser($user);
        $employeeIds = $employees->pluck('NIK');

        return EvaluationData::with('karyawan')
            ->whereIn('NIK', $employeeIds);
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('disciplineyayasantable-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            // ->dom('Bfrtip')
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
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
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
                ->addClass('text-center')
                ->addClass('align-middle'),
            Column::make('remark')
                ->title('Remark Reject')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle')
                ->orderable(false),
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'DisciplineYayasanTable_' . date('YmdHis');
    }
}
