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
                                    @if($user_id_create === Auth::user()->id)
                                        @if ($status == 1 && $status != -1)
                                            <a href="{{ route("purchaserequest.edit", $id) }}" class="btn btn-primary me-2">
                                                <i class="bx bx-edit"></i> Edit
                                            </a>
                                            <div class="modal fade" id="delete-pr-modal-{{ $id }}" tabindex="-1" role="dialog" aria-labelledby="deletePrModal" aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Delete Confirmation</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Are you sure you want to delete <span class="fw-semibold">{{ $doc_num }}</span>?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                            <form action="{{ route("purchaserequest.delete", $id) }}" method="POST" style="display: inline;">
                                                                @csrf
                                                                @method("DELETE")
                                                                <button type="submit" class="btn btn-danger">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#delete-pr-modal-{{ $id }}">
                                                <i class="bx bx-trash-alt" ></i> <span class="d-none d-sm-inline">Delete</span>
                                            </button>
                                        @endif
                                    @endif
                                ')
            ->addColumn('select_all', '<input type="checkbox" class="form-check-input" id="checkbox{{$id}}-{{$status}}-{{$doc_num}}" />')
            // ->addColumn('status', '@if($pr->status === -1)
            //                         <span class="badge text-bg-danger px-3 py-2 fs-6">REJECTED</span>
            //                     @elseif($pr->status === 0)
            //                         <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR PREPARATION</span>
            //                     @elseif($pr->status === 1)
            //                         <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR DEPT HEAD</span>
            //                     @elseif($pr->status === 2)
            //                         <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR VERIFICATION</span>
            //                     @elseif($pr->files === null)
            //                         <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING ATTACHMENT</span>
            //                     @elseif($pr->status === 3)
            //                         <span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR DIRECTOR</span>
            //                     @elseif($pr->status === 4)
            //                         <span class="badge text-bg-success px-3 py-2 fs-6">APPROVED</span>
            //                     @endif ')
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
        return $model->whereNotNull('autograph_1')
        ->whereNotNull('autograph_2')
        ->whereNotNull('autograph_3')->newQuery();
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
                    // ->orderBy(1)
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        // Button::make('pdf'),
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
                ->addClass('text-center')
                ->addClass('align-middle'),
            Column::make('pr_no')->addClass('text-center align-middle'),
            Column::make('date_pr')->addClass('text-center align-middle'),
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
                        } else if(data == 0) {
                            return \'<span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR PREPARATION</span>\'
                        } else if(data == 1){
                            return \'<span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR DEPT HEAD</span>\'
                        } else if(data == 2){
                            return \'<span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR VERIFICATION</span>\'
                        } else if(data == 3){
                            return \'<span class="badge text-bg-warning px-3 py-2 fs-6">WAITING FOR DIRECTOR</span>\'
                        } else if(data == 4){
                            return \'<span class="badge text-bg-success px-3 py-2 fs-6">APPROVED</span>\'
                        }
                    }
                    return data;
                }'
            )->exportable(false),
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
