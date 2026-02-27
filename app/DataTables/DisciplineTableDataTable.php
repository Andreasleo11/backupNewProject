<?php

namespace App\DataTables;

use App\Domain\Discipline\Services\DepartmentEmployeeResolver;
use App\Domain\Discipline\Services\DisciplineScoreCalculatorService;
use App\Models\EvaluationData;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Database\Query\Builder as BaseQueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DisciplineTableDataTable extends DataTable
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
            ->addColumn('totalkehadiran', function (EvaluationData $row) {
                // Attendance score = 40 − (Alpha×10 + Izin×2 + Sakit + Telat×0.5)
                $deduction = ($row->Alpha * 10) + ($row->Izin * 2) + $row->Sakit + ($row->Telat * 0.5);
                return max(0, 40 - $deduction);
            })
            ->addColumn('totaldiscipline', function (EvaluationData $row) use ($calculator) {
                // Sum old-system criteria scores (kerajinan, kerapian, prestasi, loyalitas, perilaku)
                $scores = [
                    'kerajinan_kerja' => $row->kerajinan_kerja,
                    'kerapian_kerja'  => $row->kerapian_kerja,
                    'prestasi'        => $row->prestasi,
                    'loyalitas'       => $row->loyalitas,
                    'perilaku_kerja'  => $row->perilaku_kerja,
                ];
                // calculateTotalOld includes attendance deduction + base 40
                // Here we only want the discipline portion (no base, no penalties)
                // so we just map scores manually using the OLD_SCORE_MAPS
                return $calculator->calculateTotalOld($scores, $row) - 40 + (
                    ($row->Alpha * 10) + ($row->Izin * 2) + $row->Sakit + ($row->Telat * 0.5)
                );
            })
            ->addColumn('grade', function (EvaluationData $row) {
                return match (true) {
                    $row->total >= 91             => 'A',
                    $row->total >= 71             => 'B',
                    $row->total >= 61             => 'C',
                    default                       => 'D',
                };
            })
            ->addColumn('action', function (EvaluationData $row) {
                $disabled = $row->is_lock ? 'disabled' : '';
                return '<button class="btn btn-primary" data-bs-toggle="modal" '
                     . 'data-bs-target="#edit-discipline-modal-' . $row->id . '" '
                     . $disabled . '><i class="bx bx-edit"></i></button>';
            })
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * Delegates to DepartmentEmployeeResolver so that the department-access
     * rules live in ONE place (the Domain layer), not duplicated here.
     */
    public function query(EvaluationData $model): QueryBuilder|BaseQueryBuilder
    {
        $user     = Auth::user();
        $resolver = app(DepartmentEmployeeResolver::class);

        // resolveForUser() handles all role/dept/special-user cases
        // (special user IDs, dept head access, email-based access)
        $employees   = $resolver->resolveForUser($user);
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
            ->setTableId('disciplinetable-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(1, 'asc')
            // ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
            ])
            ->parameters([
                'initComplete' => 'function() {
                            introJs().setOptions({
                                steps: [{
                                    title: "Welcome",
                                    intro: "Welcome to the Discipline Evaluation Page",
                                },
                                {
                                    element: document.querySelector(".buttons-excel"),
                                    title: "First Step",
                                    intro: "You need to <b>export the data</b> using by clicking this export button",
                                    position: "top",
                                },
                                {
                                    element: document.querySelector(".btn-upload"),
                                    title: "Last but not least",
                                    intro: "Upload the excel file that <b>filled with grades</b>. Voila!",
                                    position: "right",
                                }],
                                dontShowAgain: true,
                                disableInteraction: true,
                                showBullets: false,
                                dontShowAgainCookieDays: 30,
                            }).start();
                        }',
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
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
            Column::make('kerajinan_kerja')
                ->addClass('align-middle text-center')
                ->title('Kinerja Kerja'),
            Column::make('kerapian_kerja')->addClass('align-middle text-center')->title('Kerapian'),
            Column::make('loyalitas')->addClass('align-middle text-center'),
            Column::make('perilaku_kerja')
                ->addClass('align-middle text-center')
                ->title('Etika dan Kesopanan'),
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
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'DisciplineTable_' . date('YmdHis');
    }
}
