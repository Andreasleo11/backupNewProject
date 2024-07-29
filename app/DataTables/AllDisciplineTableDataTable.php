<?php

namespace App\DataTables;

use App\Models\EvaluationData;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class AllDisciplineTableDataTable extends DataTable
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
     * @param \App\Models\AllDisciplineTable $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(EvaluationData $model): QueryBuilder
    {
        return $model::with('karyawan')->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('alldisciplinetable-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(1,'asc')
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
           
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'AllDisciplineTable_' . date('YmdHis');
    }
}
