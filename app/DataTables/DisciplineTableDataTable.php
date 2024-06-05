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
        if($kerapian_pakaian === "A")
        {
            $total += 10;
        }
        elseif($kerapian_pakaian === "B")
        {
            $total += 7.5;
        }
        elseif($kerapian_pakaian === "C")
        {
            $total += 5;
        }
        elseif($kerapian_pakaian === "D")
        {
            $total += 2.5;
        }
        elseif($kerapian_pakaian === "E")
        {
            $total += 0;
        }

        if($kerapian_rambut === "A")
        {
            $total += 10;
        }
        elseif($kerapian_rambut === "B")
        {
            $total += 7.5;
        }
        elseif($kerapian_rambut === "C")
        {
            $total += 5;
        }
        elseif($kerapian_rambut === "D")
        {
            $total += 2.5;
        }
        elseif($kerapian_rambut === "E")
        {
            $total += 0;
        }

        if($kerapian_sepatu === "A")
        {
            $total += 10;
        }
        elseif($kerapian_sepatu === "B")
        {
            $total += 7.5;
        }
        elseif($kerapian_sepatu === "C")
        {
            $total += 5;
        }
        elseif($kerapian_sepatu === "D")
        {
            $total += 2.5;
        }
        elseif($kerapian_sepatu === "E")
        {
            $total += 0;
        }

        if($prestasi === "A")
        {
            $total += 10;
        }
        elseif($prestasi === "B")
        {
            $total += 7.5;
        }
        elseif($prestasi === "C")
        {
            $total += 5;
        }
        elseif($prestasi === "D")
        {
            $total += 2.5;
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

        @endphp {{ $total }}')
        ->addColumn('action', '<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#edit-discipline-modal-{{str_replace(\' \', \'\',$id)}}"><i class="bx bx-edit"></i></button>
        ')
        ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\EvaluationData $mod   el
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(EvaluationData $model): QueryBuilder
    {
        if (Auth::user()->is_head == 1 && Auth::user()->department_id == 15) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '600');
                })->newQuery();
        }
        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 25) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '351');
                })->newQuery();
        }
        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 6) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '311');
                })->newQuery();
        }
        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 9) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '500');
                })->newQuery();
        }
        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 11) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '390');
                })->newQuery();
        }
        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 16) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '363');
                })->newQuery();
        }
        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 20) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '362');
                })->newQuery();
        }
        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 19) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '361');
                })->newQuery();
        }
        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 18) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '350');
                })->newQuery();
        }
        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 24) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '331');
                })->newQuery();
        }
        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 17) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '330');
                })->newQuery();
        }
        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 5) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '320');
                })->newQuery();
        }
        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 7) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '310');
                })->newQuery();
        }
        elseif (Auth::user()->email === "ani_apriani@daijo.co.id") {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '310');
                })->newQuery();
        }

        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 8) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '200');
                })->newQuery();
        }
        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 3) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '100');
                })->newQuery();
        }
        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 2) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '340');
                })->newQuery();
        }
        elseif (Auth::user()->is_head == 1 && Auth::user()->department_id == 1) {
            // Get data for department 340
            return $model::with('karyawan')
                ->whereHas('karyawan', function ($query) {
                    $query->where('Dept', '341');
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
                    ->orderBy(1,'asc')
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel')->addClass('animated-button'),
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
            Column::make('Department')
                ->data('karyawan.Dept')
                ->searchable(false)
                ->addClass('align-middle')->orderable(false),
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
            Column::make('totalkehadiran')
                ->title('Total Nilai Kehadiran')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle')->orderable(false),
            Column::make('kerajinan_kerja'),
            Column::make('kerapian_pakaian'),
            Column::make('kerapian_rambut'),
            Column::make('kerapian_sepatu'),
            Column::make('prestasi'),
            Column::make('loyalitas'),
            Column::make('totaldiscipline')
            ->title('Total Nilai Kedisiplinan')
            ->searchable(false)
            ->exportable(false)
            ->addClass('align-middle')->orderable(false),
            Column::make('total')
            ->exportable(false),

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
        return 'DisciplineTable_' . date('YmdHis');
    }
}
