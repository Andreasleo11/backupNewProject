<?php

namespace App\DataTables;

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
    /**
     * Build DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
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
                            '(CASE '.
                                implode(
                                    ' ',
                                    array_map(
                                        function ($key, $label) {
                                            return "WHEN status = $key THEN '$label'";
                                        },
                                        array_keys($this->statusMap),
                                        $this->statusMap,
                                    ),
                                ).
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
        $user = auth()->user();
        $userDepartmentName = $user->department->name ?? null;
        $isPersonaliaHead = $userDepartmentName === 'PERSONALIA' && $user->is_head === 1;
        $isHead = $user->is_head === 1;
        $isPurchaser = $user->specification->name === 'PURCHASER';
        $isGM = $user->is_gm === 1;

        // Initialize the query
        $query = $model->newQuery()->with('files', 'createdBy');

        $query->where(function ($q) use ($userDepartmentName, $user, $isPersonaliaHead, $isGM, $isHead, $isPurchaser) {
            if ($isPersonaliaHead) {
                $q->where(function ($query) {
                    $query
                        ->whereNotNull("autograph_1")
                        ->where(function ($query) {
                            $query
                                ->whereNotNull("autograph_2")
                                ->where("branch", "JAKARTA")
                                ->orWhere(function ($query) {
                                    $query->where("type", "factory")->where("branch", "KARAWANG");
                                });
                        })
                        ->whereNotNull("autograph_5")
                        ->where(function ($query) {
                            $query
                                ->whereNull("autograph_3")
                                ->orWhereNotNull("autograph_3")
                                ->where(function ($query) {
                                    $query
                                        ->where("to_department", "Personnel")
                                        ->where("type", "office")
                                        ->orWhere("to_department", "Computer");
                                });
                        })
                        ->orWhere("from_department", "PERSONALIA");
                });
            } elseif ($isGM) {
                $q
                    ->whereNotNull("autograph_1")
                    ->whereNull("autograph_6")
                    ->where(function ($query) use ($userDepartmentName) {
                        $query->where("type", "factory");
                        if ($userDepartmentName === "MOULDING") {
                            $query->where("from_department", "MOULDING");
                        } else {
                            $query->where("from_department", "!=", "MOULDING");
                        }
                    });
                // Pawarid case
                if ($userDepartmentName !== "PLASTIC INJECTION") {
                    $q->whereNotNull("autograph_2");
                }
            } elseif ($isHead) {
                $q->where(function ($query) use ($userDepartmentName) {
                    $query->where("from_department", $userDepartmentName);
                });

                if ($userDepartmentName === "PURCHASING") {
                    $q->orWhere("to_department", ucwords(strtolower($userDepartmentName)));
                } elseif ($userDepartmentName === "LOGISTIC") {
                    $q->orWhere("from_department", "STORE");
                }
            } elseif ($isPurchaser) {
                $q->where(function ($query) {
                    $query
                        ->where(function ($query) {
                            $query->where(function ($query) {
                                $query->where("type", "office")->orWhere("from_department", "MOULDING");
                            });
                        })
                        ->orWhere(function ($query) {
                            $query->where("type", "factory");
                        });
                });

                if ($userDepartmentName === "COMPUTER" || $userDepartmentName === "PURCHASING") {
                    $q->where("to_department", ucwords(strtolower($userDepartmentName)));
                } elseif ($user->email === "nur@daijo.co.id") {
                    $q->where("to_department", "Maintenance");
                } elseif ($userDepartmentName === "PERSONALIA") {
                    $q->where("to_department", "Personnel");
                }

                $q->where(function ($query) use ($user){
                    $query->whereNotNull("autograph_1")->orWhere("user_id_create", $user->id);
                });
            } elseif ($user->role->name === "SUPERADMIN") {
                $q->whereNot("from_department", "ADMIN");
            } else {
                $q->where(function ($subQuery) use ($userDepartmentName, $user) {
                    $subQuery->where("from_department", $userDepartmentName);

                    if (auth()->user()->department->name === "QA") {
                        $subQuery
                            ->orWhere("from_department", "QC")
                            ->orWhere("user_id_create", $user->id);
                    }
                });
            }

            $q->orWhere('user_id_create', $user->id);
        });

        return $query;
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
        return 'PurchaseRequests_'.date('YmdHis');
    }
}
