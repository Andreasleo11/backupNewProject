<?php

namespace App\DataTables;

use App\Models\sapInventoryFg;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class sapInventoryFgDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'sapinventoryfg.action')
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     */
    public function query(sapInventoryFg $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('sapinventoryfg-table')
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
            Column::make('item_code'),
            Column::make('item_name'),
            Column::make('item_group'),
            Column::make('day_set_pps'),
            Column::make('setup_time'),
            Column::make('cycle_time'),
            Column::make('cavity'),
            Column::make('safety_stock'),
            Column::make('daily_limit'),
            Column::make('stock'),
            Column::make('total_spk'),
            Column::make('production_min_qty'),
            Column::make('standar_packing'),
            Column::make('pair'),
            Column::make('man_power'),
            Column::make('warehouse'),
            Column::make('process_owner'),
            Column::make('owner_code'),
            Column::make('special_condition'),
            Column::make('fg_code_1'),
            Column::make('fg_code_2'),
            Column::make('wip_code'),
            Column::make('material_percentage'),
            Column::make('continue_production'),
            Column::make('family'),
            Column::make('material_group'),
            Column::make('old_mould'),
            Column::make('packaging'),
            Column::make('bom_level'),
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'sapInventoryFg_'.date('YmdHis');
    }
}
