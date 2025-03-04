<?php

namespace App\DataTables;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class EmployeeDataTable extends DataTable
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
            @if(auth()->user())
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#edit-employee-modal{{$id}}"><i class="bx bx-edit"></i></button>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete-confirmation-modal-{{str_replace(\' \', \'\',$id)}}"><i class="bx bx-trash"></i></button>
            @endif
        ')
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Employee $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Employee $model): QueryBuilder
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
                    ->setTableId('employee-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax(route('employee-dashboard.getEmployeesData'))
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reload'),
                    ])
                    ->parameters([
                        'autoWidth'  => false,
                        'responsive' => true,
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array
     */
    public function getColumns(): array
    {
        // Build the base columns
        $columns = [
            Column::make('id'),
            Column::make('NIK'),
            Column::make('Nama'),
            Column::make('Gender'),
            Column::make('Branch'),
            Column::make('Dept'),
            Column::make('start_date'),
            Column::make('end_date'),
            Column::make('status'),
            Column::make('jatah_cuti_tahun'),
        ];

        // Conditionally add the action column if a user is authenticated
        if (auth()->user()) {
            $columns[] = Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center')
                ->addClass('align-middle');
        }

        return $columns;
    }


    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'Employee_' . date('YmdHis');
    }
}
