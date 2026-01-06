<?php

namespace App\DataTables;

use App\Application\PurchaseRequest\Services\PurchaseRequestQueryScoper;
use App\Models\PurchaseRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PurchaseRequestsDataTable extends DataTable
{
    public function __construct(
        private readonly PurchaseRequestQueryScoper $queryScoper
    ) {}

    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return \Yajra\DataTables\EloquentDataTable
     */
    protected $statusMap = [
        1 => 'WAITING FOR DEPT HEAD',
        2 => 'WAITING FOR VERIFICATOR',
        3 => 'WAITING FOR DIRECTOR',
        4 => 'APPROVED',
        5 => 'REJECTED',
        6 => 'WAITING FOR PURCHASER',
        7 => 'WAITING FOR GM',
        8 => 'DRAFT',
    ];

    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', 'purchaserequests.action')
            ->editColumn('status', function ($pr) {
                return view('partials.pr-status-badge', ['pr' => $pr])->render();
            })
            ->editColumn('action', function ($pr) {
                return view('partials.pr-action-buttons', [
                    'pr' => $pr,
                    'user' => auth()->user(),
                ])->render();
            })
            ->editColumn('date_pr', function ($pr) {
                return Carbon::parse($pr->date_pr)->setTimezone('Asia/Jakarta')->format('d-m-Y');
            })
            ->editColumn('approved_at', function ($pr) {
                return $pr->approved_date
                    ? Carbon::parse($pr->approved_at)
                        ->setTimezone('Asia/Jakarta')
                        ->format('d-m-Y (H:i)')
                    : '';
            })
            ->searchPane(
                'branch',
                PurchaseRequest::query()
                    ->select('branch as value', 'branch as label')
                    ->distinct()
                    ->get(),
                function (\Illuminate\Database\Eloquent\Builder $query, array $values) {
                    return $query->whereIn('branch', $values);
                },
            )
            ->searchPane(
                'from_department',
                PurchaseRequest::query()
                    ->select('from_department as value', 'from_department as label')
                    ->distinct()
                    ->get(),
                function (\Illuminate\Database\Eloquent\Builder $query, array $values) {
                    return $query->whereIn('from_department', $values);
                },
            )
            ->searchPane(
                'to_department',
                PurchaseRequest::query()
                    ->select('to_department as value', 'to_department as label')
                    ->distinct()
                    ->get(),
                function (\Illuminate\Database\Eloquent\Builder $query, array $values) {
                    return $query->whereIn('to_department', $values);
                },
            )
            ->searchPane(
                'status',
                PurchaseRequest::query()
                    ->select(
                        'status as value',
                        DB::raw(
                            '(CASE ' .
                                implode(
                                    ' ',
                                    array_map(
                                        function ($key, $label) {
                                            return "WHEN status = $key THEN '$label'";
                                        },
                                        array_keys($this->statusMap),
                                        $this->statusMap,
                                    ),
                                ) .
                                ' ELSE status END) as label',
                        ),
                    )
                    ->distinct()
                    ->get(),
            )
            ->rawColumns(['action', 'status'])
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     */
    public function query(PurchaseRequest $model): QueryBuilder
    {
        $query = $model->newQuery()->with('files', 'createdBy');

        return $this->queryScoper->scopeForUser(auth()->user(), $query);
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('purchaserequests-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('PBflrtip')
            ->addColumnDef([
                'searchPanes' => [
                    'show' => true,
                    'viewTotal' => false,
                    'viewCount' => false,
                ],
            ])
            ->orderBy(3)
            // ->selectStyleSingle()
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
            Column::make('id'),
            Column::make('doc_num'),
            Column::make('branch'),
            Column::make('date_pr'),
            Column::make('from_department'),
            Column::make('to_department'),
            Column::make('pr_no'),
            Column::make('supplier'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),
            Column::computed('status')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),
            Column::make('approved_at')->title('Approved Date')->data('approved_at'),
            Column::make('po_number'),
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'PurchaseRequests_' . date('YmdHis');
    }
}
