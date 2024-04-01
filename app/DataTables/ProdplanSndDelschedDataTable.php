<?php

namespace App\DataTables;

use App\Models\ProdplanSndDelsched;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ProdplanSndDelschedDataTable extends DataTable
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
            ->addColumn('action', 'prodplansnddelsched.action')
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ProdplanSndDelsched $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ProdplanSndDelsched $model): QueryBuilder
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
                    ->setTableId('prodplansnddelsched-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(1)
                    ->selectStyleSingle()
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
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
            Column::make('color')->data('color')->renderRaw('function(data, type, row, meta){
                if (type === \'display\') {
                    if (data === "light") {
                        return \'<span class="badge rounded-pill text-bg-success px-3 py-2 fs-6 fw-medium"> Aman </span>\';
                    } else if (data === "danger" ) {
                        return \'<span class="badge rounded-pill text-bg-danger px-3 py-2 fs-6 fw-medium">Danger</span>\';
                    } else if (data === "warning"){
                        return \'<span class="badge rounded-pill text-bg-warning px-3 py-2 fs-6 f w-medium">Warning</span>\';
                    } else if (data === "success"){
                        return \'<span class="badge rounded-pill text-bg-success px-3 py-2 fs-6 f w-medium">Selesai</span>\';
                    } 
                }
                return data; // Return the original data for other types
            }'),
            Column::make('actual_deldate'),
            Column::make('remarks_leadtime'),
            Column::make('delivery_date'),
            Column::make('item_code'),
            Column::make('item_name'),
            Column::make('pair_code'),
            Column::make('pair_name'),
            Column::make('prior_bom_level'),
            Column::make('outstanding'),
            Column::make('status'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'ProdplanSndDelsched_' . date('YmdHis');
    }
}
