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
            ->addColumn('action', '<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#edit-discipline-yayasan-modal-{{str_replace(\' \', \'\',$id)}}"  {{ ($is_lock === 1) ? "disabled" : ""  }}><i class="bx bx-edit"></i></button>
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
        if (Auth::user()->department_id == 25) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '351')
                        ->where('status', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->is_gm) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('status', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->department_id == 21) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '311')
                        ->where('status', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->department_id == 11) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('dept', '390')
                        ->where('status', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->department_id == 16) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '363')
                        ->where('status', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->department_id == 20) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '362')
                        ->where('status', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->department_id == 19) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '361')
                        ->where('status', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->department_id == 18) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '350')
                        ->where('status', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->department_id == 24) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '331')
                        ->where('status', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->department_id == 17) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '330')
                        ->where('status', 'YAYASAN');
                })->newQuery();
        } elseif (Auth::user()->department_id == 2) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '340')
                        ->where('status', 'YAYASAN');
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
            Column::make('Alpha')
                ->exportable(false),
            Column::make('Telat')
                ->exportable(false),
            Column::make('Izin')
                ->exportable(false),
            Column::make('Sakit')
                ->exportable(false),
            Column::make('kemampuan_kerja'),
            Column::make('kecerdasan_kerja'),
            Column::make('qualitas_kerja'),
            Column::make('disiplin_kerja'),
            Column::make('kepatuhan_kerja'),
            Column::make('lembur'),
            Column::make('efektifitas_kerja'),
            Column::make('relawan'),
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
