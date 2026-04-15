<?php

namespace App\DataTables;

use App\Application\PurchaseRequest\Queries\PurchaseRequestQueryBuilder;
use App\Models\PurchaseRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
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
                $date = Carbon::parse($pr->date_pr)->setTimezone('Asia/Jakarta');
                $absolute = $date->format('d-m-Y');
                $relative = $date->diffForHumans();

                return "<div><div class='font-medium text-slate-700'>{$relative}</div><div class='text-[10px] text-slate-400'>{$absolute}</div></div>";
            })
            ->editColumn('approved_at', function ($pr) {
                return $pr->approved_at
                    ? Carbon::parse($pr->approved_at)
                        ->setTimezone('Asia/Jakarta')
                        ->format('d-m-Y (H:i)')
                    : '';
            })
            ->addColumn('workflow_status', function ($pr) {
                if (! $pr->workflow_status) {
                    return '<span class="text-slate-400 text-xs">-</span>';
                }

                return view('partials.workflow-status-badge', ['pr' => $pr])->render();
            })
            ->addColumn('document', function ($pr) {
                $prNo = $pr->pr_no ? e($pr->pr_no) : '<span class="text-slate-400 italic">No PR Num</span>';
                $branch = $pr->branch?->value ?? ($pr->branch ?? 'HQ');
                $maker = $pr->createdBy?->name ?? 'System';

                return "
                    <div class='flex flex-col gap-0.5'>
                        <div class='flex items-center gap-1.5'>
                            <span class='font-bold text-slate-900 tracking-tight'>{$prNo}</span>
                            <span class='text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 font-bold border border-slate-200 uppercase'>{$branch}</span>
                        </div>
                        <div class='text-[10px] text-slate-400 font-medium flex items-center gap-1'>
                            <i class='bx bx-user text-xs'></i>
                            <span>{$maker}</span>
                        </div>
                    </div>
                ";
            })
            ->addColumn('items_routing', function ($pr) {
                $count = $pr->items_count ?? 0;
                $from = $pr->from_department ? e($pr->from_department) : 'Unknown';
                $to = $pr->to_department->value ?? ($pr->to_department ?? 'Unknown');

                $countBadge = "<span class='inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold text-indigo-700 ring-1 ring-inset ring-indigo-700/10'>{$count} Items</span>";

                return "
                    <div class='flex flex-col gap-1'>
                        <div>{$countBadge}</div>
                        <div class='text-[10px] text-slate-400 font-medium whitespace-nowrap overflow-hidden text-ellipsis max-w-[180px]'>
                            <span class='text-slate-500'>{$from}</span>
                            <i class='bx bx-right-arrow-alt mx-0.5'></i>
                            <span class='text-indigo-600 font-semibold'>{$to}</span>
                        </div>
                    </div>
                ";
            })
            ->editColumn('supplier', function ($pr) {
                if (! $pr->supplier) {
                    return '<span class="text-slate-400 italic text-xs">Not Specified</span>';
                }

                return '<div class="truncate max-w-[200px] text-sm text-slate-700" title="' . e($pr->supplier) . '">' . e($pr->supplier) . '</div>';
            })
            ->rawColumns(['checkbox', 'action', 'status', 'workflow_status', 'document', 'items_routing', 'supplier', 'date_pr'])
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
            ->dom('rtip')
            ->pageLength(25)
            ->orderBy(1);
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
                ->title('Document & Maker')
                ->addClass('align-middle')
                ->width('200px'),
            Column::make('date_pr')
                ->title('Requested')
                ->addClass('align-middle')
                ->width('120px'),
            Column::computed('items_routing')
                ->title('Items & Routing')
                ->addClass('align-middle')
                ->width('200px'),
            Column::make('supplier')
                ->title('Supplier')
                ->width('180px')
                ->addClass('align-middle'),
            Column::computed('workflow_status')
                ->title('Status')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center align-middle')
                ->width('150px'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center align-middle')
                ->width('100px'),

            // Hidden data columns for searchability
            Column::make('po_number')->visible(false),
            Column::make('branch')->visible(false),
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
