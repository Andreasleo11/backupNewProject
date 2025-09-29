<?php

namespace App\DataTables;

use App\Models\Report;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\SearchPane;
use Yajra\DataTables\Services\DataTable;

class DirectorQaqcReportsDataTable extends DataTable
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
                '<a href="{{ route("director.qaqc.detail", ["id" => $id]) }}" class="btn btn-secondary">
                                        <i class="bx bx-info-circle" ></i> Detail
                                    </a>
                                    ',
            )
            ->addColumn(
                'select_all',
                '<input type="checkbox" class="form-check-input" id="checkbox{{$id}}-{{$is_approve}}-{{$doc_num}}" />',
            )
            ->editColumn('rec_date', '{{ \Carbon\Carbon::parse($rec_date)->format(\'d-m-Y\') }}')
            ->editColumn(
                'verify_date',
                '{{ \Carbon\Carbon::parse($verify_date)->format(\'d-m-Y\') }}',
            )
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     */
    public function query(Report $model): QueryBuilder
    {
        return $model::where(function ($query) {
            $query->approved()->orWhere(function ($query) {
                $query->waitingApproval()->orWhere(function ($query) {
                    $query->rejected();
                });
            });
        })
            ->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('director-reports-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            // ->dom('Bfrtip')
            ->orderBy(7, 'desc')
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                // Button::raw('copy')->titleAttr('Copy Data to Clipboard'),
                // Button::make('pdf'),
                Button::make('print'),
                // Button::make('reset'),
                Button::make('reload'),
                // Button::make('searchPanes'),
            ]);
        // ->searchPanes(SearchPane::make())
        // ->addColumnDef([
        //     'targets' => '_all',
        //     'searchPanes' => [
        //         'show' => true,
        //         'vieTotal' => false,
        //         'viewCount' => false,
        //     ],
        // ])
        // ->dom('PBfrtip')
    }

    /**
     * Get the dataTable columns definition.
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
            Column::make('doc_num')->addClass('text-center align-middle'),
            Column::make('invoice_no')->addClass('text-center align-middle'),
            Column::make('customer')->addClass('text-center align-middle'),
            Column::make('rec_date')->addClass('text-center align-middle'),
            Column::make('verify_date')->addClass('text-center align-middle'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center')
                ->addClass('text-center align-middle'),
            Column::make('is_approve')
                ->title('Status')
                ->data('is_approve')
                ->addClass('text-center align-middle')
                ->renderRaw(
                    'function(data, type, row, meta){
                    if (type === \'display\') {
                        if (data === 1) {
                            return \'<span class="badge rounded-pill text-bg-success px-3 py-2 fs-6 fw-medium">APPROVED</span>\';
                        } else if (data === 0) {
                            return \'<span class="badge rounded-pill text-bg-danger px-3 py-2 fs-6 fw-medium">REJECTED</span>\';
                        } else {
                            return \'<span class="badge rounded-pill text-bg-warning px-3 py-2 fs-6 fw-medium">WAITING TO BE APPROVED</span>\';
                        }
                    }
                    return data; // Return the original data for other types
                }',
                )
                ->exportable(false),
            Column::make('approved_at')
                ->title('Approved Date')
                ->data('approved_at')
                ->addClass('text-center align middle')->renderRaw('function(data, type, row, meta){
                    if (type === \'display\') {
                        if (data === null) {
                            return \'-\';
                        }
                    }
                    return data; // Return the original data for other types
                }'),
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'DirectorReport_'.date('YmdHis');
    }
}
