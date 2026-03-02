<?php

namespace App\DataTables;

use App\Models\EvaluationData;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class EvaluationDataDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'evaluationdata.action')
            ->editColumn('Month', function ($row) {
                return $row->Month ? $row->Month->format('d/m/Y') : '-';
            })
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     */
    public function query(EvaluationData $model): QueryBuilder
    {
        return $model::with('karyawan')->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('evaluationdata-table')
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
            Column::make('id'),
            Column::make('NIK'),
            Column::make('Name')
                ->data('karyawan.name')
                ->searchable(false)
                ->addClass('align-middle')
                ->orderable(false),
            Column::make('Department')
                ->data('karyawan.dept_code')
                ->searchable(false)
                ->addClass('align-middle')
                ->orderable(false),
            Column::make('Month'),
            Column::make('Alpha'),
            Column::make('Telat'),
            Column::make('Izin'),
            Column::make('Sakit'),
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'EvaluationData_' . date('YmdHis');
    }
}
