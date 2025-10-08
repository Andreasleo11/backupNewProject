<?php

namespace App\DataTables;

use App\Models\EvaluationData;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
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
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn(
                'totalkehadiran',
                '
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
        {{ $all }}',
            )
            ->addColumn(
                'totaldiscipline',
                '@php

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

        @endphp {{ $total }}',
            )

            ->addColumn(
                'grade',
                '
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
        {{ $grade }}',
            )
            ->addColumn(
                'action',
                '
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#edit-discipline-modal-{{str_replace(\' \', \'\',$id)}}" {{ ($is_lock === 1) ? "disabled" : ""  }} ><i class="bx bx-edit"></i></button>
        ',
            )
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     */
    public function query(EvaluationData $model): QueryBuilder
    {
        $isHead = Auth::user()->is_head;
        $userDepartment = Auth::user()->department->name;

        if ($isHead && $userDepartment == 'COMPUTER') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '600')
                        ->where('NIK', '!=', '06060')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'MAINTENANCE MACHINE') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '351')
                        ->where('NIK', '!=', '07180')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'PPIC') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '311')
                        ->where('NIK', '!=', '05932')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'PE') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '500')
                        ->where('NIK', '!=', '00015')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'PLASTIC INJECTION') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '390')
                        ->where('NIK', '!=', '06054')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'MOULDING') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '363')
                        ->where('NIK', '!=', '06361')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'ASEEMBLY') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '362')
                        ->where('NIK', '!=', '00238')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'SECOND PROCESS') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '361')
                        ->where('NIK', '!=', '00021')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'MAINTENANCE') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '350')
                        ->where('NIK', '!=', '00299')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'LOGISTIC') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where(function ($query) {
                            $query->where('Dept', '331')->orWhere('Dept', '330');
                        })
                        ->where('NIK', '!=', '00179')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'STORE') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '330')
                        ->where('NIK', '!=', '06974')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'PURCHASING') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '320')
                        ->where('NIK', '!=', '07119')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'PERSONALIA') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '310')
                        ->where('NIK', '!=', '00001')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif (Auth::user()->email === 'ani_apriani@daijo.co.id') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '310')
                        ->where('NIK', '!=', '00001')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'BUSINESS') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '200')
                        ->where('NIK', '!=', '00145')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'ACCOUNTING') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '100')
                        ->where('NIK', '!=', '05994')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif (Auth::user()->id = 120) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where(function ($query) {
                            $query->where('Dept', '340')->orWhere('Dept', '341');
                        })
                        ->where('NIK', '!=', '00033')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'QC') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '340')
                        ->where('NIK', '!=', '06960')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        } elseif ($isHead && $userDepartment == 'QA') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query
                        ->where('Dept', '341')
                        ->where('NIK', '!=', '07000')
                        ->whereNotIn('status', [
                            'YAYASAN',
                            'YAYASAN KARAWANG',
                            'MAGANG',
                            'MAGANG KARAWANG',
                        ]);
                })
                ->newQuery();
        }
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
                ->data('karyawan.Nama')
                ->searchable(false)
                ->addClass('align-middle text-center')
                ->orderable(false),
            Column::make('Department')
                ->data('karyawan.Dept')
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
                ->data('karyawan.status')
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
        return 'DisciplineTable_'.date('YmdHis');
    }
}
