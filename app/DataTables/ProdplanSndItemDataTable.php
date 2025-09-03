<?php

namespace App\DataTables;

use App\Models\ProdplanSndItem;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ProdplanSndItemDataTable extends DataTable
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
            ->addColumn("action", "prodplansnditem.action")
            ->setRowId("id");
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ProdplanSndItem $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ProdplanSndItem $model): QueryBuilder
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
            ->setTableId("prodplansnditem-table")
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
            Column::make("item_code"),
            Column::make("pair_code"),
            Column::make("bom_level"),
            Column::make("lead_time"),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return "ProdplanSndItem_" . date("YmdHis");
    }
}
