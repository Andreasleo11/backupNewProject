<?php

namespace App\DataTables;

use Illuminate\Support\Facades\Auth;
use App\Models\EvaluationData;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class DisciplineTableDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return \Yajra\DataTables\EloquentDataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('totalkehadiran', '
        @php

        $total = 40;

        $countalpha = $Alpha * 10;
        $countizin = $Izin * 2;
        $counttelat = $Telat * 0.5;

        $all = $total - ($countalpha + $countizin + $counttelat + $Sakit);

        if($all < 0)
        {
            $all = 0;
        }
        @endphp
        {{ $all }}')
            ->addColumn('totaldiscipline', '@php

        $total = 0;

        if($kerajinan_kerja === "A")
        {
            $total += 10;
        }
        elseif($kerajinan_kerja === "B")
        {
            $total += 7.5;
        }
        elseif($kerajinan_kerja === "C")
        {
            $total += 5;
        }
        elseif($kerajinan_kerja === "D")
        {
            $total += 2.5;
        }
        elseif($kerajinan_kerja === "E")
        {
            $total += 0;
        }
        if($kerapian_kerja === "A")
        {
            $total += 10;
        }
        elseif($kerapian_kerja === "B")
        {
            $total += 7.5;
        }
        elseif($kerapian_kerja === "C")
        {
            $total += 5;
        }
        elseif($kerapian_kerja === "D")
        {
            $total += 2.5;
        }
        elseif($kerapian_kerja === "E")
        {
            $total += 0;
        }

        if($prestasi === "A")
        {
            $total += 20;
        }
        elseif($prestasi === "B")
        {
            $total += 15;
        }
        elseif($prestasi === "C")
        {
            $total += 10;
        }
        elseif($prestasi === "D")
        {
            $total += 5;
        }
        elseif($prestasi === "E")
        {
            $total += 0;
        }

        if($loyalitas === "A")
        {
            $total += 10;
        }
        elseif($loyalitas === "B")
        {
            $total += 7.5;
        }
        elseif($loyalitas === "C")
        {
            $total += 5;
        }
        elseif($loyalitas === "D")
        {
            $total += 2.5;
        }
        elseif($loyalitas === "E")
        {
            $total += 0;
        }

        if($perilaku_kerja === "A")
        {
            $total += 10;
        }
        elseif($perilaku_kerja === "B")
        {
            $total += 7.5;
        }
        elseif($perilaku_kerja === "C")
        {
            $total += 5;
        }
        elseif($perilaku_kerja === "D")
        {
            $total += 2.5;
        }
        elseif($perilaku_kerja === "E")
        {
            $total += 0;
        }

        @endphp {{ $total }}')

            ->addColumn('grade', '
        @php

        if($total >= 91)
        {
            $grade = "A";
        }
        elseif($total >=71 && $total <=90)
        {
            $grade = "B";
        }
        elseif($total >= 61 && $total <=70)
        {
            $grade = "C";
        }
        else
        {
            $grade = "D";
        }
        @endphp
        {{ $grade }}')
            ->addColumn('action', '
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#edit-discipline-modal-{{str_replace(\' \', \'\',$id)}}" {{ ($is_lock === 1) ? "disabled" : ""  }} ><i class="bx bx-edit"></i></button>
        ')
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\EvaluationData $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(EvaluationData $model): QueryBuilder
    {
        if (Auth::user()->is_head == 1 && Auth::user()->department_id == 15) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '600')
                        ->where('NIK', '!=', '06060')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 25) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '351')
                        ->where('NIK', '!=', '05483')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 21) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '311')
                        ->where('NIK', '!=', '05932')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 9) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '500')
                        ->where('NIK', '!=', '00015')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 11) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '390')
                        ->where('NIK', '!=', '06054')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 16) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '363')
                        ->where('NIK', '!=', '06361')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 20) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '362')
                        ->where('NIK', '!=', '00238')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 19) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '361')
                        ->where('NIK', '!=', '00021')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 18) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '350')
                        ->where('NIK', '!=', '00299')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 24) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '331')
                        ->where('NIK', '!=', '00179')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 17) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '330')
                        ->where('NIK', '!=', '06974')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 5) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '320')
                        ->where('NIK', '!=', '07119')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 7) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '310')
                        ->where('NIK', '!=', '00001')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->email === "ani_apriani@daijo.co.id") {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '310')
                        ->where('NIK', '!=', '00001')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 8) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '200')
                        ->where('NIK', '!=', '00145')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 3) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '100')
                        ->where('NIK', '!=', '05994')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 2) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '340')
                        ->where('NIK', '!=', '06960')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 1) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '341')
                        ->where('NIK', '!=', '07000')
                        ->where('status', '!=', 'YAYASAN');
                })->newQuery();
        }
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
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
                        }'
            ]);
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')
                ->visible(false),
            Column::make('NIK')->title('NIK')->addClass('align-middle text-center'),
            Column::make('Name')
                ->data('karyawan.Nama')
                ->searchable(false)
                ->addClass('align-middle text-center')->orderable(false),
            Column::make('Department')
                ->data('karyawan.Dept')
                ->searchable(false)
                ->addClass('align-middle text-center')->orderable(false),
            Column::make('start_date')
                ->title('Start Date')
                ->data('karyawan.start_date')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle text-center')->orderable(false),
            Column::make('status')
                ->title('Status')
                ->data('karyawan.status')
                ->exportable(false)
                ->searchable(false)
                ->addClass('align-middle text-center')->orderable(false),
            Column::make('Month')->addClass('align-middle text-center'),
            Column::make('Alpha')
                ->exportable(false)->addClass('align-middle text-center'),
            Column::make('Telat')
                ->exportable(false)->addClass('align-middle text-center'),
            Column::make('Izin')
                ->exportable(false)->addClass('align-middle text-center'),
            Column::make('Sakit')
                ->exportable(false)->addClass('align-middle text-center'),
            Column::make('totalkehadiran')
                ->title('Total Nilai Kehadiran')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle text-center text-bg-secondary')->orderable(false),
            Column::make('kerajinan_kerja')->addClass('align-middle text-center'),
            Column::make('kerapian_kerja')->addClass('align-middle text-center'),
            Column::make('loyalitas')->addClass('align-middle text-center'),
            Column::make('perilaku_kerja')->addClass('align-middle text-center'),
            Column::make('prestasi')->addClass('align-middle text-center'),
            Column::make('totaldiscipline')
                ->title('Total Nilai Kedisiplinan')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle text-center text-bg-secondary')->orderable(false),
            Column::make('total')
                ->exportable(false)
                ->addClass('align-middle text-center'),
            Column::make('grade')
                ->title('Grade')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle text-center')->orderable(false),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('align-middle text-center'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'DisciplineTable_' . date('YmdHis');
    }
}
