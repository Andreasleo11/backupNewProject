<?php

namespace App\DataTables;

use App\Models\UtiHolidayList;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class HolidayListDataTable extends DataTable
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
        ->addColumn('action', '
                                <button data-bs-target="#edit-holiday-modal-{{ $id }}" data-bs-toggle="modal" class="btn btn-primary my-1 me-1">
                                    <i class="bx bx-edit"></i> <span class="d-none d-sm-inline">Edit</span>
                                </button>
                                <button class="btn btn-danger my-1 me-1" data-bs-toggle="modal" data-bs-target="#delete-confirmation-modal-{{ $id }}">
                                    <i class="bx bx-trash-alt"></i> <span class="d-none d-sm-inline">Delete</span>
                                </button>
         ')
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\HolidayList $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(UtiHolidayList $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->responsive(true)
                    ->setTableId('holidaylist-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        // Button::make('pdf'),
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
            Column::make('id'),
            Column::make('date'),
            Column::make('holiday_name')->title('Name'),
            Column::make('description'),
            Column::make('half_day')->title('Half Day')->data('half_day')->addClass('text-center align middle')
            ->renderRaw('function(data, type, row, meta){
                if (type === \'display\') {
                    if (data === 1) {
                        return \'Yes\';
                    } else if (data === 0) {
                        return \'No\';
                    }
                }
                return data; // Return the original data for other types
            }'),
            Column::computed('action')
            ->exportable(false)
            ->printable(false)
            ->addClass('text-center'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'HolidayList_' . date('YmdHis');
    }
}
