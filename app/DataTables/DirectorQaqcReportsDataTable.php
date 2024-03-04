<?php

namespace App\DataTables;

use App\Models\Report;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class DirectorQaqcReportsDataTable extends DataTable
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
            ->addColumn('action', '<a href="{{ route("director.qaqc.detail", ["id" => $id]) }}" class="btn btn-secondary">
                                        <i class="bx bx-info-circle" ></i> Detail
                                    </a>
                                    @if($attachment)
                                    @php
                                        $filename = basename($attachment);
                                        @endphp
                                    <a href="{{ asset("storage/attachments/" . $attachment) }}" class="btn btn-success" download="{{ $filename }}">
                                        <i class="bx bx-download"></i>
                                        Attachment
                                    </a>
                                    @endif
                                    ')
            ->addColumn('select_all', '<input type="checkbox" class="form-check-input" id="checkbox{{$id}}-{{$is_approve}}-{{$doc_num}}" />')
            // ->editColumn('status', '<span class="badge rounded-pill
            //                             @if($is_approve === 1) text-bg-success
            //                             @elseif($is_approve === 0) text-bg-danger
            //                             @else text-bg-warning
            //                             @endif
            //                             px-3 py-2 fs-6 fw-medium">
            //                             @if($is_approve === 1)
            //                                 APPROVED
            //                             @elseif($is_approve === 0)
            //                                 REJECTED
            //                             @else
            //                                 WAITING
            //                             @endif
            //                         </span>')
            ->editColumn('created_at', '{{ \Carbon\Carbon::parse($created_at)->format(\'d-m-Y\') }}')
            ->editColumn('updated_at', '{{ \Carbon\Carbon::parse($updated_at)->format(\'d-m-Y\') }}')
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Report $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Report $model): QueryBuilder
    {
        // return $model::whereNotNull('autograph_1')
        //     ->whereNotNull('autograph_2')
        //     ->whereNotNull('autograph_3')->newQuery();
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
                    ->setTableId('director-reports-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    //->dom('Bfrtip')
                    ->orderBy(10 ,'asc')
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
            Column::computed('select_all')
                ->addClass('check_all')
                ->width(50)
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center')
                ->addClass('align-middle'),
            Column::make('id')->addClass('align-middle')->addClass('text-center'),
            Column::make('doc_num')->addClass('align-middle')->addClass('text-center'),
            Column::make('invoice_no')->addClass('align-middle')->addClass('text-center'),
            Column::make('customer')->addClass('align-middle')->addClass('text-center'),
            Column::make('rec_date')->addClass('align-middle')->addClass('text-center'),
            Column::make('verify_date')->addClass('align-middle')->addClass('text-center'),
            Column::make('created_at')->addClass('align-middle')->addClass('text-center'),
            Column::make('updated_at')->addClass('align-middle')->addClass('text-center'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center')
                ->addClass('align-middle')->addClass('text-center'),
            Column::make('is_approve')->title('Status')->data('is_approve')->addClass('align-middle')->addClass('text-center')
                ->renderRaw('function(data, type, row, meta){
                    if (type === \'display\') {
                        if (data === 1) {
                            return \'<span class="badge rounded-pill text-bg-success px-3 py-2 fs-6 fw-medium">APPROVED</span>\';
                        } else if (data === 0) {
                            return \'<span class="badge rounded-pill text-bg-danger px-3 py-2 fs-6 fw-medium">REJECTED</span>\';
                        } else {
                            return \'<span class="badge rounded-pill text-bg-warning px-3 py-2 fs-6 fw-medium">WAITING</span>\';
                        }
                    }
                    return data; // Return the original data for other types
                }'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'DirectorReport_' . date('YmdHis');
    }
}
