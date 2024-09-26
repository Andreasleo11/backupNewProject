<?php

namespace App\DataTables;

use App\Models\Report;
use Carbon\Carbon;
use DeepCopy\Filter\Filter;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class VqcReportsDataTable extends DataTable
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
            ->editColumn('rec_date', '{{ \Carbon\Carbon::parse($rec_date)->format(\'d-m-Y\') }}')
            ->editColumn('verify_date', '{{ \Carbon\Carbon::parse($verify_date)->format(\'d-m-Y\') }}')
            ->setRowId('id')
            ->filter(function($query) {
                if(request()->has('month') && request('month')){
                    $month = request('month');
                    $date = Carbon::createFromFormat('m-Y', $month);
                    $query->whereMonth('rec_date', $date->month)
                            ->whereYear('rec_date', $date->year);
                }
            })
            ->addColumn('status', function($report) {
                return view('partials.vqc-status-badge', compact('report'))->render();
            })
            ->addColumn('action', function($report) {
                return view('partials.vqc-action-buttons', compact('report'))->render();
            })
            ->rawColumns(['status', 'action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Report $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Report $model): QueryBuilder
    {
        $query = Report::query();

        if(request()->has('month') && request('month')){
            $month = request('month');
            $date = Carbon::createFromFormat('m-Y', $month);
            $query->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year);
        }

        if (request()->has('status')) {
            $status = request('status');
            if ($status === 'approved') {
                $query->approved();
            } elseif ($status === 'rejected') {
                $query->rejected();
            } elseif ($status === 'waitingSignature') {
                $query->waitingSignature();
            } elseif ($status === 'waitingApproval') {
                $query->waitingApproval();
            }
        }

        return $this->applyScopes($query);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('vqcreports-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax('', null, [
                        'month' => 'function() {return $("#monthPicker").val(); }'
                    ])
                    ->dom('Bfrtip')
                    ->orderBy(4)
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
            Column::make('doc_num')->addClass('text-center align-middle')->orderable(false),
            Column::make('invoice_no')->addClass('text-center align-middle')->orderable(false),
            Column::make('customer')->addClass('text-center align-middle'),
            Column::make('rec_date')->addClass('text-center align-middle'),
            Column::make('verify_date')->addClass('text-center align-middle'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->addClass('text-center align-middle'),
            Column::make('status')->addClass('text-center align-middle')->orderable(false),
            Column::make('description')->title('Reject Reason')->addClass('text-center align-middle'),
            Column::make('approved_at')->title('Approved Date')->addClass('text-center align-middle'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'VqcReports_' . date('YmdHis');
    }
}
