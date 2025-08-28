<?php

namespace App\DataTables;

use App\Models\sapInventoryMtr;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class sapInventoryMtrDataTable extends DataTable
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
            ->addColumn("action", "sapinventorymtr.action")
            ->setRowId("id");
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\sapInventoryMtr $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(sapInventoryMtr $model): QueryBuilder
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
            ->setTableId("sapinventorymtr-table")
            ->columns($this->getColumns())
            ->minifiedAjax()
            //->dom('Bfrtip')
            ->orderBy(1)
            ->selectStyleSingle()
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
            Column::make("fg_code"),
            Column::make("material_code"),
            Column::make("material_name"),
            Column::make("bom_quantity"),
            Column::make("in_stock"),
            Column::make("item_group"),
            Column::make("vendor_code"),
            Column::make("vendor_name"),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return "sapInventoryMtr_" . date("YmdHis");
    }
}
