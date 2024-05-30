<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))

            ->addColumn('action', '<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#edit-user-modal{{$id}}"><i class="bx bx-edit"></i></button>
                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete-user-modal{{$id}}"><i class="bx bx-trash"></i></button>
                                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#resetPasswordConfirmationModal{{$id}}">Reset Password</button>
                                    ')
            ->addColumn('select_all', '<input type="checkbox" class="form-check-input" id="checkbox{{$id}}" />')
            ->editColumn('created_at', '{{ \Carbon\Carbon::parse($created_at)->format(\'d-m-Y\') }}')
            ->editColumn('updated_at', '{{ \Carbon\Carbon::parse($updated_at)->format(\'d-m-Y\') }}')
            ->setRowId('id');
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(User $model): QueryBuilder
    {
        return $model::with(['role', 'department', 'specification'])->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->responsive(true)
                    ->setTableId('users-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(1, 'asc')
                    // ->addCheckbox(['id="check{{$id}}"'])
                    // ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload'),
                        // Button::make('custom')
                        //     ->text('Select All')
                        //     ->addClass('btn btn-success')
                        //     ->action('function() {
                        //         $(".form-check-input").prop("checked", true);
                        //     }'),
                        // Button::make('custom')
                        //     ->text('Delete Selected')
                        //     ->addClass('btn btn-danger')
                        //     ->action('function() {
                        //         // Perform delete action for selected rows
                        //     }'),
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('select_all')
                ->addClass('check_all')
                ->addClass('text-center')
                ->searchable(false)
                ->printable(false)
                ->width(50),
            Column::make('id')
                ->addClass('text-center')
                ->addClass('align-middle'),
            Column::make('name')->addClass('align-middle'),
            Column::make('email')->addClass('align-middle'),
            Column::make('role')
                ->data('role.name')
                ->searchable(false)
                ->addClass('align-middle')->orderable(false),
            Column::make('department')
                ->data('department.name')
                ->searchable(false)
                ->addClass('align-middle')->orderable(false),
            Column::make('specification')
                ->data('specification.name')
                ->searchable(false)
                ->addClass('align-middle')->orderable(false),
            Column::make('created_at')->addClass('align-middle'),
            Column::make('updated_at')->addClass('align-middle'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center')
                ->addClass('align-middle'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Users_' . date('YmdHis');
    }
}
