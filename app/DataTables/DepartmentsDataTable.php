<?php

namespace App\DataTables;

use App\Models\Department;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class DepartmentsDataTable extends DataTable
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
            ->addColumn(
                "action",
                '
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#edit-department-modal-{{$id}}"><i class="bx bx-edit"></i></button>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete-department-modal-{{$id}}"><i class="bx bx-trash"></i></button>
            ',
            )
            ->editColumn(
                "created_at",
                '{{ \Carbon\Carbon::parse($created_at)->format(\'d-m-Y\') }}',
            )
            ->editColumn(
                "updated_at",
                '{{ \Carbon\Carbon::parse($updated_at)->format(\'d-m-Y\') }}',
            )
            ->setRowId("id");
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Department $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Department $model): QueryBuilder
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
            ->setTableId("departments-table")
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->dom('Bfrtip')
            ->orderBy(0, "asc")
            ->buttons([
                Button::make("excel"),
                Button::make("csv"),
                Button::make("pdf"),
                Button::make("print"),
                Button::make("reset"),
                Button::make("reload"),
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
            Column::make("id"),
            Column::make("dept_no"),
            Column::make("name"),
            Column::make("is_office")->title("At Office")->data("is_office")
                ->renderRaw('function(data, type, row, meta){
                if (type === \'display\') {
                    if (data === 1) {
                        return \'Yes\';
                    }
                    else if (data === 0) {
                        return \'No\';
                    }
                }
                return data; // Return the original data for other types
            }'),
            Column::make("created_at"),
            Column::make("updated_at"),
            Column::computed("action")
                ->exportable(false)
                ->printable(false)
                ->addClass("text-center"),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return "Departments_" . date("YmdHis");
    }
}
