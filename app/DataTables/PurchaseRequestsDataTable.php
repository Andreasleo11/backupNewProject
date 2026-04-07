<?php

namespace App\DataTables;

use App\Application\PurchaseRequest\Queries\PurchaseRequestQueryBuilder;
use App\Models\PurchaseRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PurchaseRequestsDataTable extends DataTable
{
    public function __construct(
        private readonly PurchaseRequestQueryBuilder $queryBuilder
    ) {}

    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('checkbox', function ($pr) {
                return '<input type="checkbox" class="form-checkbox h-4 w-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 pr-checkbox cursor-pointer mx-auto block" value="' . $pr->id . '">';
            })
            ->addColumn('action', 'purchaserequests.action')
            ->editColumn('status', function ($pr) {
                return view('partials.workflow-status-badge', ['pr' => $pr])->render();
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
            ->addColumn('workflow_status', function ($pr) {
                if (! $pr->workflow_status) {
                    return '<span class="text-slate-400 text-xs">-</span>';
                }

                $badge = view('partials.workflow-status-badge', ['pr' => $pr])->render();

                // Add current approver if in review
                if ($pr->workflow_status === 'IN_REVIEW' && $pr->workflow_step) {
                    return $badge . '<div class="text-[10px] text-slate-500 mt-1 text-center truncate max-w-[150px]" title="' . htmlentities($pr->workflow_step) . '">' . e($pr->workflow_step) . '</div>';
                }

                // Add actionable feedback strings directly to the datatable view for terminal states
                if (in_array($pr->workflow_status, ['REJECTED', 'RETURNED', 'CANCELED']) || $pr->is_cancel) {
                    $approval = method_exists($pr, 'approvalRequest') ? $pr->approvalRequest : null;
                    $actionStep = $approval && isset($approval->steps) ? $approval->steps->firstWhere('sequence', $approval->current_step) : null;
                    $reason = '';
                    
                    if ($pr->workflow_status === 'RETURNED') {
                        $reason = $actionStep->return_reason ?? 'Revision required';
                    } elseif ($pr->workflow_status === 'REJECTED') {
                        $reason = $actionStep->remarks ?? ($pr->description ?? '');
                    } elseif ($pr->workflow_status === 'CANCELED' || $pr->is_cancel) {
                        $reason = $pr->cancellation_reason ?? ($pr->cancel_reason ?? ($pr->description ?? ($actionStep->remarks ?? '')));
                    }

                    if ($reason) {
                        return $badge . '<div class="text-[10px] text-slate-500 mt-1 italic text-center truncate max-w-[150px]" title="' . htmlentities('Reason: ' . $reason) . '">' . e('Reason: ' . $reason) . '</div>';
                    }
                }

                return $badge;
            })
            ->addColumn('document', function ($pr) {
                // Combine PR No and Doc Num
                $prNo = $pr->pr_no ? e($pr->pr_no) : '<span class="text-slate-400 italic">No PR Num</span>';
                $docNum = $pr->doc_num ? e($pr->doc_num) : '';

                return "<div><div class='font-bold text-slate-800'>{$prNo}</div><div class='text-xs text-slate-500'>{$docNum}</div></div>";
            })
            ->addColumn('routing', function ($pr) {
                // Combine From Dept -> To Dept
                $from = $pr->from_department ? e($pr->from_department) : 'Unknown';
                $to = $pr->to_department->value ?? ($pr->to_department ?? 'Unknown');

                return "<div class='text-sm'><span class='text-slate-600'>{$from}</span> <i class='bi bi-arrow-right text-indigo-400 mx-1'></i> <span class='text-slate-800 font-medium'>{$to}</span></div>";
            })
            ->editColumn('supplier', function ($pr) {
                if (!$pr->supplier) {
                    return '<span class="text-slate-400 italic text-xs">Not Specified</span>';
                }
                return '<div class="truncate max-w-[200px] text-sm text-slate-700" title="' . e($pr->supplier) . '">' . e($pr->supplier) . '</div>';
            })
            ->rawColumns(['checkbox', 'action', 'status', 'workflow_status', 'document', 'routing', 'supplier'])
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     * Delegates all scoping and filter logic to PurchaseRequestQueryBuilder.
     */
    public function query(PurchaseRequest $model): QueryBuilder
    {
        return $this->queryBuilder->fromRequest(auth()->user(), request());
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
            ->dom('Bfrtip')
            ->orderBy(1)
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
            Column::make('checkbox')
                ->title('<input type="checkbox" id="check-all-prs" class="form-checkbox h-4 w-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer mx-auto block">')
                ->searchable(false)
                ->orderable(false)
                ->exportable(false)
                ->printable(false)
                ->width('40px')
                ->addClass('text-center align-middle'),
            Column::make('id')->visible(false),
            Column::computed('document')
                ->title('Document')
                ->exportable(false)
                ->printable(false),
            Column::make('branch'),
            Column::make('date_pr')->title('Req. Date'),
            Column::computed('routing')
                ->title('Routing')
                ->exportable(false)
                ->printable(false),
            Column::make('supplier')
                ->title('Supplier')
                ->width('200px')
                ->addClass('align-middle'),
            Column::computed('workflow_status')
                ->title('Status')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),
            Column::computed('status')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center')
                ->visible(false), // Hide legacy status column
            Column::make('po_number')->title('PO Number'),

            // Hidden columns for standard export/search purposes to still work
            Column::make('pr_no')->visible(false),
            Column::make('doc_num')->visible(false),
            Column::make('from_department')->visible(false),
            Column::make('to_department')->visible(false),
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
