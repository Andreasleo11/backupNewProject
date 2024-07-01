<?php

namespace App\DataTables;

use App\Models\PurchaseRequest;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use PDO;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class DirectorPurchaseRequestDataTable extends DataTable
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
            ->addColumn('action', '
                                    <a href="{{ route("purchaserequest.detail", ["id" => $id]) }}" class="btn btn-secondary me-2">
                                        <i class="bx bx-info-circle" ></i> Detail
                                    </a>

                                ')
            ->addColumn('select_all', '<input type="checkbox" class="form-check-input" id="checkbox{{$id}}-{{$status}}-{{$doc_num}}" />')
            ->editColumn('approved_at', '{{ $approved_at ? \Carbon\Carbon::parse($approved_at)->timezone(\'Asia/Bangkok\')->format(\'d-m-Y (h:i:s)\') : \'\' }}')
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\PurchaseRequest $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(PurchaseRequest $model): QueryBuilder
    {
        return $model
        ->where(function($query){
            $query->where('status', 4)
                ->orWhere('status', 5)
                ->orWhere('status', 3);
        })->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('purchaserequest-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(7, 'asc')
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        Button::make('pdf'),
                        Button::make('print'),
                        // Button::make('reset'),
                        // Button::make('reload')
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
            Column::computed('select_all')
                ->addClass('check_all')
                ->title('')
                ->width(50)
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center align-middle'),
            Column::make('pr_no')->addClass('text-center align-middle'),
            Column::make('date_pr')->addClass('text-center align-middle'),
            Column::make('from_department')->addClass('text-center align-middle'),
            Column::make('to_department')->addClass('text-center align-middle'),
            Column::make('supplier')->addClass('text-center align-middle'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->addClass('text-center align-middle'),
            Column::make('status')->addClass('text-center align-middle')->renderRaw(
                'function(data, type, row, meta){
                    if(type === \'display\'){
                        if(data == 5){
                            return \'<span class="badge text-bg-danger px-3 py-2 fs-6">REJECTED</span>\'
                        } else if(data == 3){
                            return \'<span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR DIRECTOR</span>\'
                        } else if(data == 4){
                            return \'<span class="badge text-bg-success px-3 py-2 fs-6">APPROVED</span>\'
                        }
                    }
                    return data;
                }'
            )->exportable(false),
            Column::make('approved_at')->title('Approved Date')->data('approved_at')->addClass('text-center align middle')
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'PurchaseRequest_' . date('YmdHis');
    }
}
