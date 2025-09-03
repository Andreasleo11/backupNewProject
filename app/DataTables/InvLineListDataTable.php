<?php

namespace App\DataTables;

use App\Models\InvLineList;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class InvLineListDataTable extends DataTable
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
                '<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#edit-line-modal{{str_replace(\' \', \'\',$line_code)}}"><i class="bx bx-edit"></i></button>
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete-confirmation-modal-{{str_replace(\' \', \'\',$line_code)}}"><i class="bx bx-trash"></i></button>
            ',
            )
            ->setRowId("id");
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\InvLineList $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(InvLineList $model): QueryBuilder
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
            ->setTableId("invlinelist-table")
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->dom('Bfrtip')
            ->orderBy(1)
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
            Column::make("line_code"),
            Column::make("line_name"),
            Column::make("departement"),
            Column::make("daily_minutes"),
            Column::computed("action")
                ->exportable(false)
                ->printable(false)
                ->addClass("text-center")
                ->addClass("align-middle"),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return "InvLineList_" . date("YmdHis");
    }
}
