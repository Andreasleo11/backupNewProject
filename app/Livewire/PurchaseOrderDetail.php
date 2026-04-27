<?php

namespace App\Livewire;

use App\Services\PdfProcessingService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PurchaseOrderDetail extends Component
{
    public $showModal = false;

    public $purchaseOrderId;

    public $purchaseOrder;

    public $pdfUrl = null;

    public $canEdit = false;

    public $canApprove = false;

    public $canReject = false;

    protected $listeners = ['openDetailModal' => 'openModal'];

    public function openModal($poId)
    {
        $this->purchaseOrderId = $poId;
        $this->loadPurchaseOrder();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetData();
    }

    public function loadPurchaseOrder()
    {
        $this->purchaseOrder = \App\Models\PurchaseOrder::with([
            'user',
            'category',
            'approvalRequest.steps' => function ($query) {
                $query->orderBy('sequence');
            },
        ])->findOrFail($this->purchaseOrderId);

        // Set permissions
        $this->canEdit = $this->purchaseOrder->getStatusEnum()->canEdit();
        $this->canApprove = $this->purchaseOrder->getStatusEnum()->canApprove() && $this->canUserApprove();
        $this->canReject = $this->purchaseOrder->getStatusEnum()->canReject() && $this->canUserReject();

        // Generate PDF preview URL if file exists
        if ($this->purchaseOrder->filename) {
            try {
                $pdfService = app(PdfProcessingService::class);
                // For preview, we'll generate a temporary signed URL or use a public URL
                $this->pdfUrl = asset('storage/pdfs/' . $this->purchaseOrder->filename);
            } catch (\Exception $e) {
                Log::warning('Could not generate PDF preview URL', [
                    'po_id' => $this->purchaseOrderId,
                    'filename' => $this->purchaseOrder->filename,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function downloadPdf()
    {
        try {
            $pdfService = app(PdfProcessingService::class);

            return $pdfService->download($this->purchaseOrderId, auth()->id());
        } catch (\Exception $e) {
            Log::error('PDF download failed', [
                'po_id' => $this->purchaseOrderId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Failed to download PDF.');
        }
    }

    public function approve()
    {
        if (! $this->canApprove) {
            session()->flash('error', 'You do not have permission to approve this purchase order.');

            return;
        }

        try {
            $poService = app(\App\Services\PurchaseOrderService::class);
            $poService->approve($this->purchaseOrderId, auth()->id());

            session()->flash('success', 'Purchase order approved successfully.');
            $this->closeModal();
            $this->dispatch('refreshDashboard');

        } catch (\Exception $e) {
            Log::error('PO approval failed', [
                'po_id' => $this->purchaseOrderId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Failed to approve purchase order.');
        }
    }

    public function reject($reason = null)
    {
        if (! $this->canReject) {
            session()->flash('error', 'You do not have permission to reject this purchase order.');

            return;
        }

        if (! $reason) {
            $reason = 'Rejected by ' . auth()->user()->name;
        }

        try {
            $pdfService = app(PdfProcessingService::class);
            $pdfService->reject($this->purchaseOrder, $reason);

            session()->flash('success', 'Purchase order rejected successfully.');
            $this->closeModal();
            $this->dispatch('refreshDashboard');

        } catch (\Exception $e) {
            Log::error('PO rejection failed', [
                'po_id' => $this->purchaseOrderId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Failed to reject purchase order.');
        }
    }

    public function edit()
    {
        if (! $this->canEdit) {
            session()->flash('error', 'This purchase order cannot be edited.');

            return;
        }

        $this->closeModal();
        $this->dispatch('openEditModal', $this->purchaseOrderId);
    }

    private function canUserApprove(): bool
    {
        // Basic permission check - in a real app, this would check roles/permissions
        return auth()->check();
    }

    private function canUserReject(): bool
    {
        // Basic permission check - in a real app, this would check roles/permissions
        return auth()->check();
    }

    private function resetData()
    {
        $this->purchaseOrderId = null;
        $this->purchaseOrder = null;
        $this->pdfUrl = null;
        $this->canEdit = false;
        $this->canApprove = false;
        $this->canReject = false;
    }

    public function render()
    {
        return view('livewire.purchase-order.purchase-order-detail');
    }
}
