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

class DisciplineYayasanTableDataTable extends DataTable
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
            ->addColumn('totaldiscipline', '@php

        $total = 0;

            if($kemampuan_kerja === "A")
            {
                $total += 17;
            }
            elseif($kemampuan_kerja === "B")
            {
                $total += 14;
            }
            elseif($kemampuan_kerja === "C")
            {
                $total += 11;
            }
            elseif($kemampuan_kerja === "D")
            {
                $total += 8;
            }
            elseif($kemampuan_kerja === "E")
            {
                $total += 0;
            }
            if($kecerdasan_kerja  === "A")
            {
                $total += 16;
            }
            elseif($kecerdasan_kerja === "B")
            {
                $total += 13;
            }
            elseif($kecerdasan_kerja === "C")
            {
                $total += 10;
            }
            elseif($kecerdasan_kerja === "D")
            {
                $total += 7;
            }
            elseif($kecerdasan_kerja === "E")
            {
                $total += 0;
            }

            if($qualitas_kerja  === "A")
            {
                $total += 11;
            }
            elseif($qualitas_kerja  === "B")
            {
                $total += 9;
            }
            elseif($qualitas_kerja  === "C")
            {
                $total += 7;
            }
            elseif($qualitas_kerja  === "D")
            {
                $total += 4;
            }
            elseif($qualitas_kerja  === "E")
            {
                $total += 0;
            }

            if($disiplin_kerja  === "A")
            {
                $total += 8;
            }
            elseif($disiplin_kerja  === "B")
            {
                $total += 6;
            }
            elseif($disiplin_kerja  === "C")
            {
                $total += 5;
            }
            elseif($disiplin_kerja  === "D")
            {
                $total += 3;
            }
            elseif($disiplin_kerja  === "E")
            {
                $total += 0;
            }

            if($kepatuhan_kerja  === "A")
            {
                $total += 10;
            }
            elseif($kepatuhan_kerja  === "B")
            {
                $total += 8;
            }
            elseif($kepatuhan_kerja  === "C")
            {
                $total += 6;
            }
            elseif($kepatuhan_kerja  === "D")
            {
                $total += 4;
            }
            elseif($kepatuhan_kerja  === "E")
            {
                $total += 0;
            }

            if($lembur  === "A")
            {
                $total += 10;
            }
            elseif($lembur  === "B")
            {
                $total += 8;
            }
            elseif($lembur  === "C")
            {
                $total += 6;
            }
            elseif($lembur  === "D")
            {
                $total += 4;
            }
            elseif($lembur  === "E")
            {
                $total += 0;
            }

            if($efektifitas_kerja   === "A")
            {
                $total += 10;
            }
            elseif($efektifitas_kerja   === "B")
            {
                $total += 8;
            }
            elseif($efektifitas_kerja   === "C")
            {
                $total += 6;
            }
            elseif($efektifitas_kerja   === "D")
            {
                $total += 4;
            }
            elseif($efektifitas_kerja   === "E")
            {
                $total += 0;
            }


                if($relawan === "A")
            {
                $total += 10;
            }
            elseif($relawan === "B")
            {
                $total += 8;
            }
            elseif($relawan === "C")
            {
                $total += 6;
            }
            elseif($relawan    === "D")
            {
                $total += 4;
            }
            elseif($relawan    === "E")
            {
                $total += 0;
            }

            if($integritas     === "A")
            {
                $total += 8;
            }
            elseif($integritas     === "B")
            {
                $total += 6;
            }
            elseif($integritas     === "C")
            {
                $total += 5;
            }
            elseif($integritas     === "D")
            {
                $total += 3;
            }
            elseif($integritas     === "E")
            {
                $total += 0;
            }

            $totalakhir = 0;
            $totalakhir = $total;


        @endphp {{ $totalakhir }}')

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
            ->addColumn('action', '<button class="btn btn-primary edit-button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#edit-discipline-yayasan-modal"
                                            data-id="{{ $id }}"
                                            {{ ($is_lock === 1) ? "disabled" : ""  }}>
                                             <i class="bx bx-edit"></i>
                                    </button>
        ')
            ->setRowId('id')
            ->setRowAttr([
                'class' => function ($row) {
                    // Directly accessing depthead
                    if (isset($row->depthead) && $row->depthead !== null) {
                        if($row->depthead === 'rejected')
                        {
                            return 'table-danger';
                        }else{
                            if(isset($row->generalmanager) && $row->generalmanager !== null){
                                return 'table-primary';
                            }else{
                            return 'table-success';
                            }
                        }


                    }

                    return '';  // Default to no style if depthead is null
                },
            ]);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\EvaluationData $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(EvaluationData $model): QueryBuilder
    {
        $userDepartment = Auth::user()->department->name;

        if ($userDepartment == 'MAINTENANCE MACHINE') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '351')
                        ->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);
                })->newQuery();
        } elseif (Auth::user()->is_gm) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);
                })->newQuery();}
        elseif (Auth::user()->email === "ani_apriani@daijo.co.id" || Auth::user()->email === "bernadett@daijo.co.id") {
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);
                })->newQuery();
        } elseif ($userDepartment == 'PPIC') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '311')
                        ->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);
                })->newQuery();
        } elseif ($userDepartment == 'PLASTIC INJECTION') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('dept', '390')
                        ->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);
                })->newQuery();
        } elseif ($userDepartment == 'MOULDING') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '363')
                        ->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);
                })->newQuery();
        } elseif ($userDepartment == 'ASSEMBLY') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '362')
                        ->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);
                })->newQuery();
        } elseif ($userDepartment == 'SECOND PROCESS') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where(function ($query) {
                        $query->where('Dept', '361')->orWhere('Dept', '362');
                    })
                        ->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);
                })->newQuery();
        } elseif ($userDepartment == 'MAINTENANCE') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '350')
                        ->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);
                })->newQuery();
        } elseif ($userDepartment == 'LOGISTIC') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where(function ($query) {
                        $query->where('Dept', '331')->orWhere('Dept', '330');
                    })
                        ->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);
                })->newQuery();
        } elseif ($userDepartment == 'STORE') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);

                    if (Auth::user()->name === 'catur') {
                        $query->where(function ($query) {
                            $query->where('Dept', '331')->orWhere('Dept', '330');
                        });
                    } else {
                        $query->where('Dept', '330');
                    }
                })->newQuery();
        } elseif (Auth::user()->email === "raditya_qa@daijo.co.id") {
            // Get data for department 341
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '341')
                        ->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);
                })->newQuery();
        } elseif ($userDepartment == 'QC' || $userDepartment == 'QA') {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->whereIn('status', ['YAYASAN', 'YAYASAN KARAWANG']);

                    if (auth()->user()->name === 'yuli') {
                        $query->where(function ($query) {
                            $query->where('Dept', '340')->orWhere('Dept', '341');
                        });
                    } else {
                        $query->where('Dept', '340');
                    }
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
            ->setTableId('disciplineyayasantable-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->dom('Bfrtip')
            ->orderBy(1)

            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload')
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
                ->visible(false)
                ->exportable(true),
            Column::make('NIK'),
            Column::make('Name')
                ->data('karyawan.Nama')
                ->searchable(false)
                ->addClass('align-middle')->orderable(false),
            Column::make('dept')
                ->addClass('align-middle'),
            Column::make('start_date')
                ->title('Start Date')
                ->data('karyawan.start_date')
                ->searchable(false)
                ->addClass('align-middle')->orderable(false),
            Column::make('status')
                ->title('Status')
                ->data('karyawan.status')
                ->searchable(false)
                ->addClass('align-middle')->orderable(false),
            Column::make('Month'),
            Column::make('Alpha'),
            Column::make('Telat'),
            Column::make('Izin'),
            Column::make('Sakit'),
            Column::make('kemampuan_kerja'),
            Column::make('kecerdasan_kerja'),
            Column::make('qualitas_kerja')
                    ->title('Kualitas Kerja'),
            Column::make('disiplin_kerja'),
            Column::make('kepatuhan_kerja'),
            Column::make('lembur'),
            Column::make('efektifitas_kerja'),
            Column::make('relawan')
                    ->title('Ringan Tangan'),
            Column::make('integritas'),
            Column::make('totaldiscipline')
                ->title('Total Nilai Kedisiplinan')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle')->orderable(false),
            Column::make('total')
                ->exportable(false),
            Column::make('grade')
                ->title('Grade')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle')->orderable(false),
            Column::make('pengawas')
                ->title('Approved By')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle')->orderable(false),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center')
                ->addClass('align-middle'),
            Column::make('remark')
                ->title('Remark Reject')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle')->orderable(false),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'DisciplineYayasanTable_' . date('YmdHis');
    }
}
