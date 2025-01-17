<?php

namespace App\DataTables;

use App\Models\EmployeeTraining;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class EmployeeTrainingDataTable extends DataTable
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
            ->addColumn('action', function($training) {
                return view('partials.employee-training-actions', compact('training'));
            })
            ->editColumn('last_training_at', function($training){
                return Carbon::parse($training->last_training_at)->format('d-m-Y');
            })
            ->filterColumn('employee_name', function ($query, $keyword) {
                $query->whereRaw("LOWER(employees.Nama) LIKE ?", ["%{$keyword}%"]);
            })
            ->filterColumn('employee_nik', function ($query, $keyword) {
                $query->whereRaw("LOWER(employees.NIK) LIKE ?", ["%{$keyword}%"]);
            })
            ->filterColumn('employee_dept', function ($query, $keyword) {
                $query->whereRaw("LOWER(employees.Dept) LIKE ?", ["%{$keyword}%"]);
            })
            ->editColumn('evaluated', function ($training) {
                return $training->evaluated
                    ? '<span class="badge bg-success">Yes</span>'
                    : '<span class="badge bg-danger">No</span>';
            })
            ->rawColumns(['evaluated'])
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\EmployeeTraining $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(EmployeeTraining $model): QueryBuilder
    {
        return $model::select([
            'employee_trainings.*',
            'employees.Nama as employee_name',
            'employees.NIK as employee_nik',
            'employees.Dept as employee_dept',
        ])
        ->join('employees', 'employees.id', '=', 'employee_trainings.employee_id');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('employeetraining-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    // ->dom('Bfrtip')
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
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
            Column::make('employee_name')->title('Name')->data('employee_name'),
            Column::make('employee_nik')->title('NIK')->data('employee_nik'),
            Column::make('employee_dept')->title('Dept')->data('employee_dept'),
            Column::make('description'),
            Column::make('last_training_at'),
            Column::make('evaluated'),
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
        return 'EmployeeTraining_' . date('YmdHis');
    }
}
