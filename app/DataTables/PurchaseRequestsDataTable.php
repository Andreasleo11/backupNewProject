<?php

namespace App\DataTables;

use App\Application\PurchaseRequest\Services\PurchaseRequestQueryScoper;
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
        private readonly PurchaseRequestQueryScoper $queryScoper
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
                    return $badge . '<div class="text-[10px] text-slate-500 mt-1">' . $pr->workflow_step . '</div>';
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
            ->rawColumns(['checkbox', 'action', 'status', 'workflow_status', 'document', 'routing'])
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     */
    public function query(PurchaseRequest $model): QueryBuilder
    {
        $query = $model->newQuery()->with([
            'files',
            'createdBy',
            'approvalRequest' => function ($q) {
                $q->select('id', 'approvable_id', 'approvable_type', 'status', 'current_step');
            },
        ]);

        // Apply Custom UI Filters from the Dropdowns
        if (request()->filled('custom_status')) {
            $statusString = request('custom_status');

            $query->where(function ($q) use ($statusString) {
                if ($statusString === 'DRAFT') {
                    $q->whereHas('approvalRequest', fn ($sub) => $sub->where('approval_requests.status', 'DRAFT'))
                      ->orWhereDoesntHave('approvalRequest');
                } elseif ($statusString === 'CANCELED') {
                    $q->where('purchase_requests.is_cancel', 1);
                } elseif ($statusString === 'IN_REVIEW') {
                    $q->whereHas('approvalRequest', fn ($sub) => $sub->where('approval_requests.status', 'IN_REVIEW'));
                } elseif ($statusString === 'APPROVED') {
                    $q->whereHas('approvalRequest', fn ($sub) => $sub->where('approval_requests.status', 'APPROVED'));
                } elseif ($statusString === 'REJECTED') {
                    $q->whereHas('approvalRequest', fn ($sub) => $sub->where('approval_requests.status', 'REJECTED'));
                }
            });

            // If it's not canceled search, make sure we only grab non-canceled PRs
            if ($statusString !== 'CANCELED') {
                $query->where(function ($q) {
                    $q->whereNull('purchase_requests.is_cancel')->orWhere('purchase_requests.is_cancel', 0);
                });
            }
        }

        if (request()->filled('custom_department')) {
            $dept = request('custom_department');
            // Based on our routing, we usually filter by target department representing where it's going
            $query->where('to_department', $dept);
        }

        if (request()->filled('custom_date')) {
            $dates = explode(' to ', request('custom_date'));
            if (count($dates) === 2) {
                $query->whereBetween('date_pr', [trim($dates[0]), trim($dates[1])]);
            } elseif (count($dates) === 1) {
                $query->whereDate('date_pr', trim($dates[0]));
            }
        }

        // Apply URL Top-Card Filters
        if (request()->filled('filter')) {
            $filter = request('filter');

            if ($filter === 'my_approval') {
                $userId = auth()->id();
                $roleIds = auth()->user()->roles->pluck('id')->toArray();

                $query->inReview()
                    ->whereHas('approvalRequest.steps', function ($q) use ($userId, $roleIds) {
                        $q->where('sequence', \Illuminate\Support\Facades\DB::raw('(SELECT current_step FROM approval_requests WHERE id = approval_steps.approval_request_id)'))
                            ->whereNull('acted_at')
                            ->where(function ($q2) use ($userId, $roleIds) {
                                $q2->where(function ($u) use ($userId) {
                                    $u->where('approver_type', 'user')
                                        ->where('approver_id', $userId);
                                })->orWhere(function ($r) use ($roleIds) {
                                    $r->where('approver_type', 'role')
                                        ->whereIn('approver_id', $roleIds);
                                });
                            });
                    });
            } elseif ($filter === 'in_review') {
                $query->inReview();
            } elseif ($filter === 'approved_month') {
                $query->workflowApproved()
                    ->whereYear('purchase_requests.updated_at', now()->year)
                    ->whereMonth('purchase_requests.updated_at', now()->month);
            }
        }

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
            Column::make('supplier')->title('Supplier'),
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
