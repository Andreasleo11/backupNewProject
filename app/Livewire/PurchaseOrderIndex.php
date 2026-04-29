<?php

namespace App\Livewire;

use App\Enums\PurchaseOrderStatus;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseOrderIndex extends Component
{
    use WithPagination;

    public $search = '';

    public $statusFilter = '';

    public $vendorFilter = '';

    public $monthFilter = '';

    public $dateFrom = '';

    public $dateTo = '';

    public $amountFrom = '';

    public $amountTo = '';

    public $creatorFilter = '';

    public $perPage = 10;

    public $perPageOptions = [10, 25, 50, 100];

    public $selectedIds = [];

    public $selectAll = false;

    public $sortBy = 'created_at';

    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'vendorFilter' => ['except' => ''],
        'monthFilter' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'amountFrom' => ['except' => ''],
        'amountTo' => ['except' => ''],
        'creatorFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingVendorFilter()
    {
        $this->resetPage();
    }

    public function updatingMonthFilter()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function updatingAmountFrom()
    {
        $this->resetPage();
    }

    public function updatingAmountTo()
    {
        $this->resetPage();
    }

    public function updatingCreatorFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->vendorFilter = '';
        $this->monthFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->amountFrom = '';
        $this->amountTo = '';
        $this->creatorFilter = '';
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
        $this->selectedIds = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function exportSelected()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'No purchase orders selected for export.');
            return;
        }

        return $this->exportPOs($this->selectedIds);
    }

    public function exportFiltered()
    {
        $poIds = $this->getPurchaseOrdersQuery()->pluck('id')->toArray();

        if (empty($poIds)) {
            session()->flash('error', 'No purchase orders found matching current filters.');
            return;
        }

        return $this->exportPOs($poIds);
    }

    private function exportPOs(array $poIds)
    {
        $purchaseOrders = \App\Models\PurchaseOrder::with(['user', 'category'])
            ->whereIn('id', $poIds)
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'purchase-orders-' . now()->format('Y-m-d-H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($purchaseOrders) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'PO Number',
                'Invoice Date',
                'Invoice Number',
                'Vendor Name',
                'Creator',
                'Status',
                'Total',
                'Currency',
                'Approved Date',
                'Created At',
            ]);

            // CSV data
            foreach ($purchaseOrders as $po) {
                fputcsv($file, [
                    $po->po_number,
                    $po->invoice_date ? $po->invoice_date->format('Y-m-d') : '',
                    $po->invoice_number ?: '',
                    $po->vendor_name,
                    $po->user?->name ?: '',
                    $po->getStatusEnum()->label(),
                    $po->total,
                    $po->currency ?: 'IDR',
                    $po->approved_date ? $po->approved_date->format('Y-m-d H:i:s') : '',
                    $po->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            // Select only visible items on current page
            $this->selectedIds = $this->getPurchaseOrdersQuery()
                ->paginate($this->perPage)
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function updatedSelectedIds()
    {
        $this->selectAll = false;
    }

    public function approveSelected()
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'No purchase orders selected.');

            return;
        }

        try {
            $poService = app(PurchaseOrderService::class);

            // Validate that selected POs can be approved (must be in IN_REVIEW status)
            $invalidPOs = \App\Models\PurchaseOrder::whereIn('id', $this->selectedIds)
                ->whereDoesntHave('approvalRequest', function ($query) {
                    $query->where('status', 'IN_REVIEW');
                })
                ->pluck('po_number')
                ->toArray();

            if (! empty($invalidPOs)) {
                session()->flash('error', 'Some selected POs cannot be approved: ' . implode(', ', $invalidPOs));

                return;
            }

            // Approve each PO
            foreach ($this->selectedIds as $poId) {
                $poService->approve($poId, auth()->id());
            }

            session()->flash('success', 'Selected purchase orders approved successfully.');
            $this->selectedIds = [];
            $this->selectAll = false;

        } catch (\Exception $e) {
            Log::error('Bulk approval failed', [
                'selected_ids' => $this->selectedIds,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Failed to approve selected purchase orders.');
        }
    }

    public function rejectSelected($reason = null)
    {
        if (empty($this->selectedIds)) {
            session()->flash('error', 'No purchase orders selected.');

            return;
        }

        if (! $reason) {
            $reason = 'Bulk rejection by ' . auth()->user()->name;
        }

        try {
            // Validate that selected POs can be rejected (must be in IN_REVIEW status)
            $invalidPOs = \App\Models\PurchaseOrder::whereIn('id', $this->selectedIds)
                ->whereDoesntHave('approvalRequest', function ($query) {
                    $query->where('status', 'IN_REVIEW');
                })
                ->pluck('po_number')
                ->toArray();

            if (! empty($invalidPOs)) {
                session()->flash('error', 'Some selected POs cannot be rejected: ' . implode(', ', $invalidPOs));

                return;
            }

            // Reject each PO using the service
            foreach ($this->selectedIds as $poId) {
                $poService->reject($poId, auth()->id(), $reason);
            }

            session()->flash('success', 'Selected purchase orders rejected successfully.');
            $this->selectedIds = [];
            $this->selectAll = false;

        } catch (\Exception $e) {
            Log::error('Bulk rejection failed', [
                'selected_ids' => $this->selectedIds,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Failed to reject selected purchase orders.');
        }
    }

    public function getPurchaseOrdersQuery()
    {
        $query = \App\Models\PurchaseOrder::select([
                'id', 'po_number', 'invoice_date', 'invoice_number', 'vendor_name',
                'creator_id', 'status', 'total', 'approved_date', 'created_at', 'updated_at'
            ])
            ->with([
                'user:id,name',
                'category:id,name',
                'approvalRequest:id,approvable_id,approvable_type,status'
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('po_number', 'like', '%' . $this->search . '%')
                        ->orWhere('vendor_name', 'like', '%' . $this->search . '%')
                        ->orWhere('invoice_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->withWorkflowStatus($this->statusFilter);
            })
            ->when($this->vendorFilter, function ($query) {
                $query->where('vendor_name', $this->vendorFilter);
            })
            ->when($this->monthFilter, function ($query) {
                $query->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$this->monthFilter]);
            })
            ->when($this->dateFrom, function ($query) {
                $query->where('invoice_date', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->where('invoice_date', '<=', $this->dateTo);
            })
            ->when($this->amountFrom, function ($query) {
                $query->where('total', '>=', $this->amountFrom);
            })
            ->when($this->amountTo, function ($query) {
                $query->where('total', '<=', $this->amountTo);
            })
            ->when($this->creatorFilter, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->creatorFilter . '%');
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        return $query;
    }

    public function getPurchaseOrdersProperty()
    {
        return $this->getPurchaseOrdersQuery()->paginate($this->perPage);
    }

    public function getFiltersProperty()
    {
        return [
            'statuses' => [
                '' => 'All Statuses',
                'DRAFT' => 'Draft',
                'IN_REVIEW' => 'In Review',
                'APPROVED' => 'Approved',
                'REJECTED' => 'Rejected',
                'CANCELLED' => 'Cancelled',
            ],
            'vendors' => ['' => 'All Vendors'] + 
                \App\Models\PurchaseOrder::distinct()
                    ->pluck('vendor_name', 'vendor_name')
                    ->toArray(),
            'months' => ['' => 'All Months'] + 
                \App\Models\PurchaseOrder::selectRaw("DISTINCT DATE_FORMAT(invoice_date, '%Y-%m') as month_value, DATE_FORMAT(invoice_date, '%M %Y') as month_label")
                    ->whereNotNull('invoice_date')
                    ->orderByRaw("DATE_FORMAT(invoice_date, '%Y-%m') DESC")
                    ->pluck('month_label', 'month_value')
                    ->toArray(),
            'creators' => ['' => 'All Creators'] + 
                \App\Models\PurchaseOrder::with('user')
                    ->whereHas('user')
                    ->distinct()
                    ->join('users', 'purchase_orders.creator_id', '=', 'users.id')
                    ->pluck('users.name', 'users.name')
                    ->toArray(),
        ];
    }

    public function render()
    {
        return view('livewire.purchase-order.index', [
            'purchaseOrders' => $this->purchaseOrders,
            'filters' => $this->filters,
            'perPageOptions' => $this->perPageOptions,
        ]);
    }
}
