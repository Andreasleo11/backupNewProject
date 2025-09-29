<?php

namespace App\DataTables;

use App\Models\DelschedFinalWip;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class WipFinalDsDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'wipfinalds.action')
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param  \App\Models\WipFinalD  $model
     */
    public function query(DelschedFinalWip $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('wipfinalds-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            // ->dom('Bfrtip')
            ->orderBy(1)

            ->selectStyleSingle()
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload'),
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('status')->data('status')->renderRaw('function(data, type, row, meta){
                if (type === \'display\') {
                    if (data === "Finish") {
                        return \'<span class="badge rounded-pill text-bg-success px-3 py-2 fs-6 fw-medium"> Finish </span>\';
                    } else if (data === "Danger" ) {
                        return \'<span class="badge rounded-pill text-bg-danger px-3 py-2 fs-6 fw-medium">Danger</span>\';
                    } else if (data === "Warning"){
                        return \'<span class="badge rounded-pill text-bg-warning px-3 py-2 fs-6 f w-medium">Warning</span>\';
                    }
                }
                return data; // Return the original data for other types
            }'),
            Column::make('id'),
            Column::make('fglink_id'),
            Column::make('so_number'),
            Column::make('delivery_date'),
            Column::make('customer_code'),
            Column::make('customer_name'),
            Column::make('item_code'),
            Column::make('item_name'),
            Column::make('outstanding_del'),
            Column::make('wip_code'),
            Column::make('wip_name'),
            Column::make('departement'),
            Column::make('bom_level'),
            Column::make('bom_quantity'),
            Column::make('req_quantity'),
            Column::make('stock_wip'),
            Column::make('balance_wip'),
            Column::make('outstanding_wip'),
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'WipFinalDs_'.date('YmdHis');
    }
}
