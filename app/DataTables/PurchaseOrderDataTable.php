<?php

namespace App\DataTables;

use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\SearchPane;
use Yajra\DataTables\Services\DataTable;

class PurchaseOrderDataTable extends DataTable
{
    protected $statusMap = [
        1 => 'WAITING',
        2 => 'APPROVED',
        3 => 'REJECTED',
    ];

    /**
     * Build DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        $rawColumns = ['status_label', 'action'];
        $dataTable = (new EloquentDataTable($query))
            ->addColumn('creator_name', function ($po) {
                return $po->user ? $po->user->name : 'N/A'; // Use the related user's name
            })
            ->editColumn('created_at', function ($po) {
                return Carbon::parse($po->created_at)->format('d-m-Y H:i'); // Format data consistently
            })
            ->editColumn('invoice_date', function ($po) {
                return Carbon::parse($po->invoice_date)->format('d-m-Y'); // Ensure consistent date format
            })
            ->editColumn('tanggal_pembayaran', function ($po) {
                return Carbon::parse($po->tanggal_pembayaran)
                    ->setTimezone('Asia/Jakarta')
                    ->format('d-m-Y');
            })
            ->editColumn('approved_date', function ($po) {
                return $po->approved_date
                    ? Carbon::parse($po->approved_date)
                        ->setTimezone('Asia/Jakarta')
                        ->format('d-m-Y (H:i)')
                    : '';
            })
            ->addColumn('action', function ($po) {
                return view('partials.po-actions', ['po' => $po])->render();
            })
            ->editColumn('total', function ($po) {
                return number_format($po->total, 1, '.', ',');
            })
            ->addColumn('status_label', function ($po) {
                return view('partials.po-status', ['po' => $po])->render();
            })
            ->filter(function ($query) {
                $request = request();

                // Handle global search (default search bar functionality)
                $globalSearch = $request->input('search.value', null);
                if ($globalSearch) {
                    $query->where(function ($q) use ($globalSearch) {
                        $q->orWhere('po_number', 'like', "%{$globalSearch}%")
                            ->orWhere('vendor_name', 'like', "%{$globalSearch}%")
                            ->orWhere('invoice_date', 'like', "%{$globalSearch}%")
                            ->orWhere('status', 'like', "%{$globalSearch}%"); // Add columns you want to search globally
                    });
                }

                // Handle SearchPanes filter for status
                $statusFilter = $request->input('searchPanes.status', null);
                if ($statusFilter) {
                    $query->whereIn('status', $statusFilter);
                }
            })
            ->addColumn('status', function ($po) {
                return $po->status;
            })
            ->searchPane(
                'currency',
                PurchaseOrder::query()
                    ->select('currency as value', 'currency as label')
                    ->distinct()
                    ->get(),
                function (\Illuminate\Database\Eloquent\Builder $query, array $values) {
                    return $query->whereIn('currency', $values);
                },
            )
            ->searchPane(
                'status', // Use the 'status' column for the Search Pane
                fn () => collect([
                    ['value' => 1, 'label' => 'Waiting'],
                    ['value' => 2, 'label' => 'Approved'],
                    ['value' => 3, 'label' => 'Rejected'],
                    ['value' => 4, 'label' => 'Canceled'],
                ]),
                // function (\Illuminate\Database\Eloquent\Builder $query, array $values) {
                //     return $query->whereIn('status', $values); // Filter by raw integer values
                // }
            )
            ->searchPane(
                'vendor_name', // Define SearchPane for the vendor_name column
                fn () => PurchaseOrder::query()
                    ->select('vendor_name as value', 'vendor_name as label')
                    ->distinct()
                    ->get(),
                function (\Illuminate\Database\Eloquent\Builder $query, array $values) {
                    return $query->whereIn('vendor_name', $values);
                },
            )
            ->searchPane(
                'invoice_date',
                function () {
                    $dates = PurchaseOrder::query()
                        ->selectRaw(
                            "DATE_FORMAT(invoice_date, '%Y-%m') as value, DATE_FORMAT(invoice_date, '%M %Y') as label",
                        )
                        ->distinct()
                        ->orderByRaw("DATE_FORMAT(invoice_date, '%Y-%m')")
                        ->get();

                    return $dates
                        ->map(function ($date) {
                            return [
                                'value' => $date->value, // e.g., "2024-01"
                                'label' => $date->label, // e.g., "January 2024"
                            ];
                        })
                        ->toArray();
                },
                function (\Illuminate\Database\Eloquent\Builder $query, array $values) {
                    // Filter by the selected month-year values
                    return $query->where(function ($q) use ($values) {
                        foreach ($values as $value) {
                            $q->orWhere('invoice_date', 'like', $value.'%'); // Match YYYY-MM format
                        }
                    });
                },
            )
            ->searchPane(
                'tanggal_pembayaran', // Use the 'tanggal_pembayaran' column for the Search Pane
                function () {
                    // Retrieve distinct month-year combinations
                    $dates = PurchaseOrder::query()
                        ->selectRaw(
                            "DATE_FORMAT(tanggal_pembayaran, '%Y-%m') as value, DATE_FORMAT(tanggal_pembayaran, '%M %Y') as label",
                        )
                        ->distinct()
                        ->orderByRaw("DATE_FORMAT(tanggal_pembayaran, '%Y-%m')")
                        ->get();

                    // Convert the results into an array of options
                    return $dates
                        ->map(function ($date) {
                            return [
                                'value' => $date->value, // e.g., "2024-01"
                                'label' => $date->label, // e.g., "January 2024"
                            ];
                        })
                        ->toArray();
                },
                function (\Illuminate\Database\Eloquent\Builder $query, array $values) {
                    // Filter by the selected month-year values
                    return $query->where(function ($q) use ($values) {
                        foreach ($values as $value) {
                            $q->orWhere('tanggal_pembayaran', 'like', $value.'%'); // Match YYYY-MM format
                        }
                    });
                },
            )
            ->searchPane(
                'category.name',
                fn () => \App\Models\PurchaseOrderCategory::query()
                    ->select('id as value', 'name as label')
                    ->distinct()
                    ->get(),
                function (\Illuminate\Database\Eloquent\Builder $query, array $values) {
                    // Filter the query based on the selected categories
                    return $query->whereIn('purchase_order_category_id', $values);
                },
            )
            ->with('totalSum', function () use ($query) {
                $selectedMonthInvoiceDate = request('searchPanes')['invoice_date'] ?? null; // Get selected invoice date from SearchPanes

                if ($selectedMonthInvoiceDate) {
                    // Filter records to match the selected month-year
                    $query->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [
                        $selectedMonthInvoiceDate,
                    ]);
                }

                $selectedMonthTanggalPembayaran =
                    request('searchPanes')['tanggal_pembayaran'] ?? null; // Get selected invoice date from SearchPanes

                if ($selectedMonthTanggalPembayaran) {
                    // Filter records to match the selected month-year
                    $query->whereRaw("DATE_FORMAT(tanggal_pembayaran, '%Y-%m') = ?", [
                        $selectedMonthTanggalPembayaran,
                    ]);
                }

                $status = request('searchPanes')['status'] ?? null;
                if ($status) {
                    $query->where('status', $status);
                }

                $vendorName = request('searchPanes')['vendor_name'] ?? null;
                if ($vendorName) {
                    $query->where('vendor_name', $vendorName);
                }

                $categoryId = request('searchPanes')['category.name'] ?? null;
                if ($categoryId) {
                    $query->where('purchase_order_category_id', $categoryId);
                }

                // // Apply all filters dynamically from DataTable's request
                // $request = request();
                // $globalSearch = $request->input('search.value', null);
                // if ($globalSearch) {
                //     $query->where(function ($q) use ($globalSearch) {
                //         $q->orWhere('po_number', 'like', "%{$globalSearch}%")
                //         ->orWhere('vendor_name', 'like', "%{$globalSearch}%")
                //         ->orWhere('invoice_date', 'like', "%{$globalSearch}%")
                //         ->orWhere('status', 'like', "%{$globalSearch}%")
                //         ->orWhere('invoice_number', 'like', "%{$globalSearch}%");
                //         // ->orWhere('category', 'like', "%{$globalSearch}%");
                //     });
                // }

                return $query->sum('total'); // Calculate the sum for filtered records
            })

            ->rawColumns($rawColumns)
            ->setRowId(function ($po) {
                return 'row-'.$po->id; // Set a unique row ID
            });
        // Conditionally add the checkbox column for directors
        if (auth()->user()->specification->name === 'DIRECTOR') {
            $dataTable->addColumn('checkbox', function ($po) {
                return '<input type="checkbox" class="row-checkbox" value="'.$po->id.'">';
            });
            $dataTable->rawColumns(array_merge(['checkbox'], $rawColumns));
        }

        return $dataTable;
    }

    /**
     * Get query source of dataTable.
     */
    public function query(PurchaseOrder $model): QueryBuilder
    {
        return $model::with(['category'])
            ->newQuery()
            ->select([
                'id',
                'purchase_order_category_id',
                'creator_id',
                'po_number',
                'vendor_name',
                'invoice_date',
                'invoice_number',
                'tanggal_pembayaran',
                'currency',
                'total',
                'created_at',
                'approved_date',
                'status',
            ])
            ->with('user');

        // Apply month filter if provided
        $month = $this->request()->get('month');
        if ($month) {
            $query->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$month]);
        }
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html(): HtmlBuilder
    {
        $buttons = [
            Button::make('excel')
                ->text(
                    '<i class=\'bx bx-spreadsheet\' style\'color:#ffffff\' ></i> Export to Excel',
                )
                ->attr(['class' => 'btn btn-secondary btn-sm']),
            Button::make('csv'),
            Button::make('pdf'),
            Button::make('print'),
            // Button::make('reset'),
            // Button::make('reload')
        ];

        // Add conditional buttons for directors
        if (auth()->user()->specification->name === 'DIRECTOR') {
            $buttons = array_merge(
                [
                    Button::make()
                        ->text('<i class=\'bx bx-check-circle\'></i> Approve Selected')
                        ->attr([
                            'id' => 'approve-selected-btn',
                            'class' => 'btn btn-success btn-sm',
                        ]),
                    Button::make()
                        ->text('<i class=\'bx bx-x-circle\'></i> Reject Selected')
                        ->attr(['id' => 'reject-selected-btn', 'class' => 'btn btn-danger btn-sm']),
                ],
                $buttons,
            );
        }

        return $this->builder()
            ->setTableId('purchaseorder-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('po.index'))
            ->searchPanes(SearchPane::make())
            ->addColumnDef([
                'targets' => '_all',
                'searchPanes' => [
                    'show' => true,
                    'viewTotal' => false,
                    'viewCount' => false,
                ],
            ])
            ->dom('PBflrtip')
            ->orderBy(3)
            ->buttons($buttons);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        $columns = [
            Column::make('po_number')->searchPanes(false),
            Column::make('category')->data('category.name')->orderable(false)->searchable(false),
            Column::make('vendor_name'),
            Column::make('invoice_date'),
            Column::make('invoice_number')->searchPanes(false),
            Column::make('tanggal_pembayaran'),
            Column::make('currency'),
            Column::make('total')->searchPanes(false),
            Column::make('created_at')
                ->data('created_at')
                ->title('Uploaded at')
                ->searchPanes(false),
            Column::make('creator_name')
                ->data('creator_name')
                ->title('Uploaded by')
                ->searchPanes(false),
            Column::make('approved_date')->searchPanes(false),
            Column::make('status')->visible(false),
            Column::computed('status_label')
                ->title('Status')
                ->searchPanes(false)
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center')
                ->searchPanes(false),
        ];

        // Conditionally add the checkbox column for directors
        if (auth()->user()->specification->name === 'DIRECTOR') {
            array_unshift(
                $columns,
                Column::computed('checkbox')
                    ->title('<input type="checkbox" id="select-all">')
                    ->exportable(false)
                    ->printable(false)
                    ->addClass('text-center')
                    ->width(10)
                    ->orderable(false)
                    ->searchable(false)
                    ->data('checkbox')
                    ->searchPanes(false),
            );
        }

        return $columns;
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'PurchaseOrder_'.date('YmdHis');
    }
}
