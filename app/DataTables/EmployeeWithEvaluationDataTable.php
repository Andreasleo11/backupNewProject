<?php

namespace App\DataTables;

use App\Models\Employee;
use App\Models\EmployeeWithEvaluation;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class EmployeeWithEvaluationDataTable extends DataTable
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
            ->filterColumn('NIK', function ($query, $keyword) {
                $query->where('employees.NIK', 'like', "%{$keyword}%");
            })
            ->filterColumn('Nama', function ($query, $keyword) {
                $query->where('employees.Nama', 'like', "%{$keyword}%");
            })
            ->filterColumn('Dept', function ($query, $keyword) {
                $query->where('employees.Dept', 'like', "%{$keyword}%");
            })
            ->filterColumn('Branch', function ($query, $keyword) {
                $query->where('employees.Branch', 'like', "%{$keyword}%");
            })
            ->filterColumn('employee_status', function ($query, $keyword) {
                $query->where('employees.employee_status', 'like', "%{$keyword}%");
            })
            ->filterColumn('Month', function ($query, $keyword) {
                $query->where('latest_evaluation.Month', 'like', "%{$keyword}%");
            })
            ->addColumn('action', function($employee){
                return view('partials.employee-with-evaluation-actions', ['employee' => $employee])->render();
            })
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\EmployeeWithEvaluation $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Employee $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->leftJoinSub(
                DB::table('evaluation_datas')
                    ->select('NIK', 'Month', 'Alpha', 'Telat', 'Izin', 'Sakit', 'total')
                    ->whereIn('id', function ($query) {
                        $query->select(DB::raw('MAX(id)'))
                            ->from('evaluation_datas')
                            ->groupBy('NIK');
                    }),
                'latest_evaluation',
                'employees.NIK',
                '=',
                'latest_evaluation.NIK'
            )
            ->select(
                'employees.id',
                'employees.NIK',
                'employees.Nama',
                'employees.Dept',
                'employees.Branch',
                'employees.employee_status',
                'latest_evaluation.Month',
                'latest_evaluation.Alpha',
                'latest_evaluation.Telat',
                'latest_evaluation.Izin',
                'latest_evaluation.Sakit',
                'latest_evaluation.total'
            );

        // Apply filters
        if ($this->request()->get('branch')) {
            $query->where('employees.Branch', $this->request()->get('branch'));
        }

        if ($this->request()->get('dept')) {
            $query->where('employees.Dept', $this->request()->get('dept'));
        }

        if ($this->request()->get('status')) {
            $query->where('employees.employee_status', $this->request()->get('status'));
        }

        return $query;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('employeewithevaluation-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax('', null, [
                        'branch' => '$("#branchFilter").val() || null',
                        'dept' => '$("#deptFilter").val() || null',
                        'status' => '$("#statusFilter").val() || null',
                    ])
                    //->dom('Bfrtip')
                    ->orderBy(1)
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
            Column::make('nik')->title('NIK')->data('NIK'),
            Column::make('nama')->title('Name')->data('Nama'),
            Column::make('dept')->title('Dept')->data('Dept'),
            Column::make('branch')->title('Branch')->data('Branch'),
            Column::make('status')->title('Status')->data('employee_status'),
            Column::make('Month'),
            Column::make('Alpha')->searchable(false),
            Column::make('Telat')->searchable(false),
            Column::make('Izin')->searchable(false),
            Column::make('Sakit')->searchable(false),
            Column::make('total')->searchable(false),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'EmployeeWithEvaluation_' . date('YmdHis');
    }
}
