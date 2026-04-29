<?php

namespace App\Livewire;

use App\Enums\PurchaseOrderStatus;
use App\Services\PurchaseOrderService;
use Illuminate\Database\Eloquent\Builder;
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

    public $perPage = 10;

    public $perPageOptions = [10, 25, 50, 100];

    public $selectedIds = [];

    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'vendorFilter' => ['except' => ''],
        'monthFilter' => ['except' => ''],
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

    public function updatingPerPage()
    {
        $this->resetPage();
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

            // Validate that selected POs can be approved
            $invalidPOs = \App\Models\PurchaseOrder::whereIn('id', $this->selectedIds)
                ->whereIn('status', [
                    PurchaseOrderStatus::APPROVED->legacyValue(),
                    PurchaseOrderStatus::REJECTED->legacyValue(),
                    PurchaseOrderStatus::CANCELLED->legacyValue(),
                ])
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
            // Validate that selected POs can be rejected
            $invalidPOs = \App\Models\PurchaseOrder::whereIn('id', $this->selectedIds)
                ->whereIn('status', [
                    PurchaseOrderStatus::APPROVED->legacyValue(),
                    PurchaseOrderStatus::REJECTED->legacyValue(),
                ])
                ->pluck('po_number')
                ->toArray();

            if (! empty($invalidPOs)) {
                session()->flash('error', 'Some selected POs cannot be rejected: ' . implode(', ', $invalidPOs));

                return;
            }

            // Reject each PO
            \App\Models\PurchaseOrder::whereIn('id', $this->selectedIds)
                ->update([
                    'status' => PurchaseOrderStatus::REJECTED->legacyValue(),
                    'reason' => $reason,
                ]);

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
        $query = \App\Models\PurchaseOrder::with(['user', 'category', 'approvalRequest'])
            ->when($this->search, function (Builder $query) {
                $query->where(function (Builder $q) {
                    $q->where('po_number', 'like', '%' . $this->search . '%')
                        ->orWhere('vendor_name', 'like', '%' . $this->search . '%')
                        ->orWhere('invoice_number', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function (Builder $query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->vendorFilter, function (Builder $query) {
                $query->where('vendor_name', $this->vendorFilter);
            })
            ->when($this->monthFilter, function (Builder $query) {
                $query->whereRaw("DATE_FORMAT(invoice_date, '%Y-%m') = ?", [$this->monthFilter]);
            })
            ->orderBy('created_at', 'desc');

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
                PurchaseOrderStatus::PENDING_APPROVAL->legacyValue() => PurchaseOrderStatus::PENDING_APPROVAL->label(),
                PurchaseOrderStatus::APPROVED->legacyValue() => PurchaseOrderStatus::APPROVED->label(),
                PurchaseOrderStatus::REJECTED->legacyValue() => PurchaseOrderStatus::REJECTED->label(),
                PurchaseOrderStatus::CANCELLED->legacyValue() => PurchaseOrderStatus::CANCELLED->label(),
            ],
            'vendors' => ['' => 'All Vendors'] + \App\Models\PurchaseOrder::distinct()
                ->pluck('vendor_name', 'vendor_name')
                ->toArray(),
            'months' => ['' => 'All Months'] + \App\Models\PurchaseOrder::selectRaw("DISTINCT DATE_FORMAT(invoice_date, '%Y-%m') as month_value, DATE_FORMAT(invoice_date, '%M %Y') as month_label")
                ->orderByRaw("DATE_FORMAT(invoice_date, '%Y-%m') DESC")
                ->pluck('month_label', 'month_value')
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
