<?php

namespace App\DataTables;

use App\Models\PurchaseRequest;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AccountingPurchaseRequestDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn(
                'action',
                '
                                        <a href="{{ route("purchaserequest.detail", ["id" => $id]) }}" class="btn btn-secondary me-2">
                                            <i class="bx bx-info-circle" ></i> Detail
                                        </a>
                                        <a href="{{ route("purchaserequest.exportToPdf", $id) }}"
                                                            class="btn btn-outline-success my-1">
                                                            <i class=\'bx bxs-file-pdf\'></i> <span
                                                                class="d-none d-sm-inline">Export PDF</span>
                                                        </a>
                                    ',
            )
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     */
    public function query(PurchaseRequest $model): QueryBuilder
    {
        return $model->where('status', 4)->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('purchaserequest-table')
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
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('pr_no')->addClass('text-center align-middle'),
            Column::make('date_pr')->addClass('text-center align-middle'),
            Column::make('from_department')->addClass('text-center align-middle'),
            Column::make('to_department')->addClass('text-center align-middle'),
            Column::make('supplier')->addClass('text-center align-middle'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center')
                ->addClass('text-center align-middle'),
            Column::make('status')
                ->addClass('text-center align-middle')
                ->renderRaw(
                    'function(data, type, row, meta){
                    if(type === \'display\'){
                        if(data == 4){
                            return \'<span class="badge text-bg-success px-3 py-2 fs-6">APPROVED</span>\'
                        }
                    }
                    return data;
                }',
                )
                ->exportable(false)
                ->addClass('text-center align-middle'),
            Column::make('description')->addClass('text-center align-middle'),
            Column::make('approved_at')->addClass('text-center align-middle'),
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'PurchaseRequest_'.date('YmdHis');
    }
}
