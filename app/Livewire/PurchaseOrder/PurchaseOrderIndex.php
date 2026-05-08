<?php

namespace App\Livewire\PurchaseOrder;

use App\Jobs\PurchaseOrder\ProcessPurchaseOrderApprovalJob;
use App\Jobs\PurchaseOrder\ProcessPurchaseOrderRejectionJob;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Cache;
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

    public $amountFrom = '';

    public $amountTo = '';

    public $creatorFilter = '';

    public $categoryFilter = '';
    
    public $invoicingFilter = '';


    public $perPage = 10;

    public $perPageOptions = [10, 25, 50, 100];

    public $selectedIds = [];

    public $selectAll = false;

    public $sortBy = 'created_at';

    public $sortDirection = 'desc';

    public $processingIds = []; // Track IDs currently being processed in background

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'vendorFilter' => ['except' => ''],
        'monthFilter' => ['except' => ''],
        'amountFrom' => ['except' => ''],
        'amountTo' => ['except' => ''],
        'creatorFilter' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'invoicingFilter' => ['except' => ''],

        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    // Modal properties
    public $showDetailModal = false;

    public $selectedPurchaseOrder;

    public $modalLoading = false;

    public $pdfUrl = null;

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

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingInvoicingFilter()
    {
        $this->resetPage();
    }


    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortByColumn($column)
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
        $this->creatorFilter = '';
        $this->categoryFilter = '';
        $this->invoicingFilter = '';
        $this->amountFrom = '';
        $this->amountTo = '';
        $this->sortBy = 'created_at';
        $this->sortDirection = 'desc';
        $this->selectedIds = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function clearAmountFilters()
    {
        $this->amountFrom = '';
        $this->amountTo = '';
        $this->resetPage();
    }

    public function openDetailModal($poId)
    {
        $this->showDetailModal = true;
        $this->modalLoading = true;
        $this->selectedPurchaseOrder = null; // Reset previous data

        $this->loadPurchaseOrderForModal($poId);
    }

    private function loadPurchaseOrderForModal($poId)
    {
        $this->selectedPurchaseOrder = PurchaseOrder::with([
            'user',
            'category',
            'approvalRequest.steps' => function ($query) {
                $query->orderBy('sequence');
            },
            'approvalRequest.actions.causer',
        ])->findOrFail($poId);

        // Generate PDF preview URL if file exists
        $this->generatePdfUrl();

        $this->modalLoading = false;
    }

    private function generatePdfUrl()
    {
        $this->pdfUrl = null;

        if ($this->selectedPurchaseOrder && $this->selectedPurchaseOrder->filename) {
            try {
                $this->pdfUrl = asset('storage/pdfs/' . $this->selectedPurchaseOrder->filename);
            } catch (\Exception $e) {
                Log::warning('Could not generate PDF preview URL', [
                    'po_id' => $this->selectedPurchaseOrder->id,
                    'filename' => $this->selectedPurchaseOrder->filename,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function downloadPdf()
    {
        try {
            $pdfService = app(\App\Services\PdfProcessingService::class);

            return $pdfService->download($this->selectedPurchaseOrder->id, auth()->id());
        } catch (\Exception $e) {
            Log::error('PDF download failed', [
                'po_id' => $this->selectedPurchaseOrder->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Failed to download PDF.');
        }
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->modalLoading = false;
        $this->selectedPurchaseOrder = null;
        $this->pdfUrl = null;
    }

    public function approvePurchaseOrder()
    {
        $this->authorize('approve', $this->selectedPurchaseOrder);

        try {
            $poService = app(PurchaseOrderService::class);
            $poService->approve($this->selectedPurchaseOrder->id, auth()->id());

            session()->flash('success', 'Purchase order approved successfully.');
            $this->closeDetailModal();
            $this->resetPage();

        } catch (\Exception $e) {
            Log::error('PO approval failed', [
                'po_id' => $this->selectedPurchaseOrder->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Failed to approve purchase order: ' . $e->getMessage());
        }
    }

    public function rejectPurchaseOrder($reason = null)
    {
        $this->authorize('reject', $this->selectedPurchaseOrder);

        if (! $reason) {
            $reason = 'Rejected by ' . auth()->user()->name;
        }

        try {
            $poService = app(PurchaseOrderService::class);
            $poService->reject($this->selectedPurchaseOrder->id, auth()->id(), $reason);

            session()->flash('success', 'Purchase order rejected successfully.');
            $this->closeDetailModal();
            $this->resetPage();

        } catch (\Exception $e) {
            Log::error('PO rejection failed', [
                'po_id' => $this->selectedPurchaseOrder->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Failed to reject purchase order: ' . $e->getMessage());
        }
    }

    public function editPurchaseOrder()
    {
        $this->authorize('update', $this->selectedPurchaseOrder);

        $this->closeDetailModal();
    }

    public function refresh()
    {
        $this->resetPage();
    }

    public function refreshData()
    {
        // Force refresh of computed properties by updating a dependency
        $this->selectedIds = [];
        $this->selectAll = false;
        // This will trigger Livewire to recalculate all computed properties
    }

    public function handlePoCreated($poData)
    {
        $this->exitFormMode();
        $this->refreshData();
        session()->flash('success', 'Purchase Order created successfully!');
    }

    public function handlePoUpdated($poData)
    {
        $this->exitFormMode();
        $this->refreshData();
        session()->flash('success', 'Purchase Order updated successfully!');
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
        $purchaseOrders = PurchaseOrder::with(['user', 'category'])
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
            $count = count($this->selectedIds);

            foreach ($this->selectedIds as $id) {
                $po = PurchaseOrder::find($id);
                if ($po && $po->getStatusEnum()->canApprove()) {
                    // Add to processing list for UI feedback
                    $this->processingIds[] = $id;

                    // Dispatch the background job
                    ProcessPurchaseOrderApprovalJob::dispatch($po, auth()->id());
                }
            }

            session()->flash('info', "Processing {$count} purchase orders in the background. Please wait...");

            $this->selectedIds = [];
            $this->selectAll = false;

        } catch (\Exception $e) {
            Log::error('Bulk approval dispatch failed', [
                'selected_ids' => $this->selectedIds,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Failed to start bulk approval: ' . $e->getMessage());
        }
    }

    /**
     * Polled by Livewire to update status of background processing
     */
    public function checkProcessingStatus()
    {
        if (empty($this->processingIds)) {
            return;
        }

        // 1. Check for background processing errors
        foreach ($this->processingIds as $key => $id) {
            if ($error = Cache::get("po_process_error_{$id}")) {
                Cache::forget("po_process_error_{$id}");

                // Remove from processingIds so we stop polling it
                unset($this->processingIds[$key]);

                $this->dispatch('notify', [
                    'type' => 'error',
                    'message' => "Processing failed for PO #{$id}: {$error}",
                ]);
            }
        }
        $this->processingIds = array_values($this->processingIds);

        if (empty($this->processingIds)) {
            return;
        }

        // 2. Check if remaining POs are still in IN_REVIEW status
        $stillProcessing = PurchaseOrder::whereIn('id', $this->processingIds)
            ->whereHas('approvalRequest', function ($q) {
                $q->where('status', 'IN_REVIEW');
            })
            ->pluck('id')
            ->toArray();

        // Find which ones are done
        $completed = array_diff($this->processingIds, $stillProcessing);

        if (! empty($completed)) {
            $this->processingIds = array_values($stillProcessing);

            if (empty($this->processingIds)) {
                session()->flash('success', 'All background approvals have been completed.');
            }
        }
    }

    public function rejectSelected($reason = null)
    {
        $idsToReject = $this->selectedIds;

        // Context check: If bulk selection is empty, check if we're in the detail modal
        if (empty($idsToReject) && $this->selectedPurchaseOrder) {
            $idsToReject = [$this->selectedPurchaseOrder->id];
        }

        if (empty($idsToReject)) {
            $this->dispatch('toast', message: 'No purchase orders selected for rejection.', type: 'error');

            return;
        }

        if (! $reason) {
            $reason = 'Rejected by ' . auth()->user()->name;
        }

        try {
            $count = count($idsToReject);

            foreach ($idsToReject as $id) {
                $po = PurchaseOrder::find($id);
                if ($po && $po->getStatusEnum()->canReject()) {
                    // Add to processing list for UI feedback
                    $this->processingIds[] = $id;

                    // Dispatch the background job
                    ProcessPurchaseOrderRejectionJob::dispatch($po, auth()->id(), $reason);
                }
            }

            if ($count === 1 && $this->selectedPurchaseOrder) {
                $this->closeDetailModal();
                $this->dispatch('toast', message: "Rejection process started for PO #{$this->selectedPurchaseOrder->po_number}...", type: 'info');
            } else {
                $this->dispatch('toast', message: "Processing {$count} rejections in the background...", type: 'info');
            }

            $this->selectedIds = [];
            $this->selectAll = false;

        } catch (\Exception $e) {
            Log::error('Bulk rejection dispatch failed', [
                'selected_ids' => $this->selectedIds,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Failed to start bulk rejection: ' . $e->getMessage());
        }
    }

    public function getPurchaseOrdersQuery()
    {
        // Use optimized query with selective field loading and relationship optimization
        $query = PurchaseOrder::query()
            ->select([
                'id', 'po_number',
                'vendor_name', 'creator_id', 'total', 'approved_date', 'created_at', 'purchase_order_category_id',
            ])
            ->with([
                'user:id,name',
                'approvalRequest.steps',
                'approvalRequest.actions',
            ])
            ->withCount('invoices')
            ->withSum('invoices as invoiced_total', 'total');


        // Optimized search across multiple fields
        if ($this->search) {
            $searchTerm = trim($this->search);
            if (strlen($searchTerm) > 0) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('po_number', 'like', '%' . $searchTerm . '%')
                        ->orWhere('vendor_name', 'like', '%' . $searchTerm . '%')
                        ->orWhereHas('user', function ($userQuery) use ($searchTerm) {
                            $userQuery->where('name', 'like', '%' . $searchTerm . '%');
                        });
                });
            }
        }

        // Optimized status filtering using the improved scope
        if ($this->statusFilter) {
            $query->withWorkflowStatus($this->statusFilter);
        }

        // Direct column filters
        if ($this->vendorFilter) {
            $query->where('vendor_name', $this->vendorFilter);
        }

        // Category filtering
        if ($this->categoryFilter) {
            $query->where('purchase_order_category_id', $this->categoryFilter);
        }

        // Creator filtering
        if ($this->creatorFilter) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->creatorFilter . '%');
            });
        }

        // Amount range filtering
        if ($this->amountFrom) {
            $query->where('total', '>=', $this->amountFrom);
        }

        if ($this->amountTo) {
            $query->where('total', '<=', $this->amountTo);
        }

        // Invoicing status filtering
        if ($this->invoicingFilter) {
            switch ($this->invoicingFilter) {
                case 'not_invoiced':
                    $query->has('invoices', '=', 0);
                    break;
                case 'partially_invoiced':
                    $query->has('invoices', '>', 0)
                        ->whereHas('invoices', function ($q) {
                            // Sum of invoices < PO total
                        })->whereRaw('(SELECT SUM(total) FROM invoices WHERE invoices.purchase_order_id = purchase_orders.id) < purchase_orders.total');
                    break;
                case 'fully_invoiced':
                    $query->whereRaw('(SELECT SUM(total) FROM invoices WHERE invoices.purchase_order_id = purchase_orders.id) >= purchase_orders.total');
                    break;
            }
        }


        // Optimized sorting with whitelist
        $sortableColumns = [
            'po_number', 'vendor_name',
            'total', 'approved_date', 'created_at',
            'purchase_order_category_id',
        ];

        if (in_array($this->sortBy, $sortableColumns)) {
            $query->orderBy($this->sortBy, $this->sortDirection);
        } else {
            $query->orderBy('created_at', 'desc'); // Default fallback
        }

        return $query;
    }

    public function getPurchaseOrdersProperty()
    {
        return $this->getPurchaseOrdersQuery()->paginate($this->perPage);
    }

    public function getFilteredTotalProperty()
    {
        return $this->getPurchaseOrdersQuery()->sum('total');
    }

    public function getStatsProperty()
    {
        $currentMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        return [
            'pending_me' => PurchaseOrder::whereHas('approvalRequest', function ($q) {
                $q->where('status', 'IN_REVIEW');
            })->get()->filter(fn ($po) => $po->getStatusEnum()->canApprove())->count(),

            'in_review' => PurchaseOrder::withWorkflowStatus('IN_REVIEW')->count(),

            'rejected_month' => PurchaseOrder::withWorkflowStatus('REJECTED')
                ->whereBetween('updated_at', [$currentMonth, $endOfMonth])
                ->count(),

            'total_valuation' => PurchaseOrder::whereBetween('created_at', [$currentMonth, $endOfMonth])
                ->sum('total'),
        ];
    }

    public function getCanBulkActionProperty()
    {
        return $this->bulkActionReason === null;
    }

    public function getBulkActionReasonProperty()
    {
        if (empty($this->selectedIds)) {
            return 'No items selected.';
        }

        // Check for items not in IN_REVIEW
        $invalidCount = PurchaseOrder::whereIn('id', $this->selectedIds)
            ->where(function ($query) {
                $query->whereDoesntHave('approvalRequest')
                    ->orWhereHas('approvalRequest', function ($q) {
                        $q->whereIn('status', ['APPROVED', 'REJECTED', 'CANCELLED', 'DRAFT']);
                    });
            })
            ->count();

        if ($invalidCount > 0) {
            return 'Selection contains items already processed or in Draft.';
        }

    }

    public function filterByStat($type)
    {
        $this->resetPage();
        $this->clearFilters();

        switch ($type) {
            case 'pending_me':
                $this->statusFilter = 'IN_REVIEW';
                // Note: The actual filtering for 'pending_me' happens in the query logic
                // if we add a specific filter property for it.
                $this->search = '';
                break;
            case 'in_review':
                $this->statusFilter = 'IN_REVIEW';
                break;
            case 'rejected':
                $this->statusFilter = 'REJECTED';
                break;
        }
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
            'vendors' => ['' => 'All Vendors'] + PurchaseOrder::query()
                ->distinct()
                ->whereNotNull('vendor_name')
                ->pluck('vendor_name', 'vendor_name')
                ->toArray(),
            'creators' => ['' => 'All Creators'] + PurchaseOrder::query()
                ->join('users', 'purchase_orders.creator_id', '=', 'users.id')
                ->distinct()
                ->pluck('users.name', 'users.name')
                ->toArray(),
            'categories' => ['' => 'All Categories'] + \App\Models\PurchaseOrderCategory::query()
                ->pluck('name', 'id')
                ->toArray(),
            'invoicing_statuses' => [
                '' => 'All Invoicing',
                'not_invoiced' => 'Not Invoiced',
                'partially_invoiced' => 'Partially Invoiced',
                'fully_invoiced' => 'Fully Invoiced',
            ],

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
