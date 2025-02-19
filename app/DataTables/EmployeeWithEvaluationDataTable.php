<?php

namespace App\DataTables;

use App\Models\Employee;
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
            ->addColumn('totalkehadiran', '
            @php

            $total = 100;

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
            ->filterColumn('NIK', function ($query, $keyword) {
                $query->where('employees.NIK', 'like', "%{$keyword}%");
            })
            ->filterColumn('Nama', function ($query, $keyword) {
                $query->where('employees.Nama', 'like', "%{$keyword}%");
            })
            ->filterColumn('department_name', function ($query, $keyword) {
                $query->where('departments.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('Branch', function ($query, $keyword) {
                $query->where('employees.Branch', 'like', "%{$keyword}%");
            })
            ->filterColumn('employee_status', function ($query, $keyword) {
                $query->where('employees.employee_status', 'like', "%{$keyword}%");
            })
            ->filterColumn('Month', function ($query, $keyword) {
                $query->where('evaluation.Month', 'like', "%{$keyword}%");
            })
            ->addColumn('action', function($employee){
                return view('partials.employee-with-evaluation-actions', ['employee' => $employee])->render();
            })
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
        $query = $model->newQuery()
            ->leftJoin('departments', 'employees.Dept', '=', 'departments.dept_no') // Join the departments table
            ->leftJoinSub(
                DB::table('evaluation_datas')
                    ->select('id','NIK', 'Month', 'Alpha', 'Telat', 'Izin', 'Sakit', 'total'),
                'evaluation',
                'employees.NIK',
                '=',
                'evaluation.NIK'
            )
            ->select(
                'employees.id',
                'employees.NIK',
                'employees.Nama',
                'employees.Gender',
                'departments.name as department_name', // Select the department name
                'employees.Branch',
                'employees.employee_status',
                'evaluation.id as evaluation_id',
                'evaluation.Month',
                'evaluation.Alpha',
                'evaluation.Telat',
                'evaluation.Izin',
                'evaluation.Sakit',
                'evaluation.total'
            );

        // \Illuminate\Support\Facades\Log::info($query->toSql());

        // Apply filters
        if ($this->request()->get('branch')) {
            $query->where('employees.Branch', $this->request()->get('branch'));
        }

        if ($this->request()->get('dept')) {
            $query->where('departments.dept_no', $this->request()->get('dept')); // Use dept_no from departments table
        }

        if ($this->request()->get('status')) {
            $query->where('employees.employee_status', $this->request()->get('status'));
        }

        if ($this->request()->get('gender')) {
            $query->where('employees.Gender', $this->request()->get('gender'));
        }

        if($this->request()->get('monthYear')) {
            $monthYear = explode("-", $this->request()->get('monthYear'));
            $month = $monthYear[0]; // Get month
            $year = $monthYear[1];  // Get year
            $query->whereMonth('Month', $month)
                ->whereYear('Month', $year);
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
                    ->minifiedAjax(route('employee-dashboard.getEmployeeWithEvaluationData'), null, [
                        'branch' => '$("#branchFilter").val() || null',
                        'dept' => '$("#deptFilter").val() || null',
                        'status' => '$("#statusFilter").val() || null',
                        'gender' => '$("#genderFilter").val() || null',
                        'monthYear' => '$("#monthYearFilter").val() || null',
                    ])
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                    ])->parameters([
                        'initComplete' => 'function() {
                            let table = $("#employeewithevaluation-table").DataTable();
                            $("#branchFilter, #deptFilter, #statusFilter, #genderFilter, #monthYearFilter").on("change", function() {
                                table.ajax.reload();
                            });
                        }',
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
            Column::make('Gender'),
            Column::make('department_name'),
            Column::make('branch')->title('Branch')->data('Branch'),
            Column::make('status')->title('Status')->data('employee_status'),
            Column::make('Month'),
            Column::make('Alpha')->searchable(false),
            Column::make('Telat')->searchable(false),
            Column::make('Izin')->searchable(false),
            Column::make('Sakit')->searchable(false),
            Column::make('totalkehadiran')
                ->title('Total Nilai Kehadiran')
                ->searchable(false)
                ->exportable(false)
                ->addClass('align-middle text-center text-bg-secondary')->orderable(false),
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
