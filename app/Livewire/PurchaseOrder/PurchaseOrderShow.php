<?php

namespace App\Livewire\PurchaseOrder;

use App\Jobs\PurchaseOrder\ProcessPurchaseOrderApprovalJob;
use App\Jobs\PurchaseOrder\ProcessPurchaseOrderRejectionJob;
use App\Models\File;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PurchaseOrderShow extends Component
{
    public $purchaseOrderId;
    public $reason = '';
    public $loading = false;
    
    public $isProcessing = false; // Background processing state

    public function mount($id)
    {
        $this->purchaseOrderId = $id;
    }

    public function getPurchaseOrderProperty()
    {
        return PurchaseOrder::with([
            'user', 
            'category', 
            'approvalRequest.actions.causer', 
            'approvalRequest.steps', 
            'downloadLogs.user',
            'latestDownloadLog.user'
        ])->findOrFail($this->purchaseOrderId);
    }

    public function getActivitiesProperty()
    {
        $po = $this->purchaseOrder;
        $activities = collect();
        
        // 1. Initial Submission
        if ($po->approvalRequest && $po->approvalRequest->submitted_at) {
            $activities->push((object)[
                'type' => 'submission',
                'date' => $po->approvalRequest->submitted_at,
                'user' => $po->user->name,
                'label' => 'Submitted for Approval',
                'icon' => 'bi-send',
                'color' => 'indigo'
            ]);
        }
        
        // 2. Approval Actions
        if ($po->approvalRequest) {
            foreach ($po->approvalRequest->actions as $action) {
                $activities->push((object)[
                    'type' => 'approval',
                    'date' => $action->created_at,
                    'user' => $action->causer->name ?? 'System',
                    'label' => 'Status: ' . $action->to_status,
                    'remarks' => $action->remarks,
                    'icon' => match($action->to_status) {
                        'APPROVED' => 'bi-check-circle',
                        'REJECTED' => 'bi-x-circle',
                        'RETURNED' => 'bi-arrow-left-right',
                        default => 'bi-info-circle'
                    },
                    'color' => match($action->to_status) {
                        'APPROVED' => 'emerald',
                        'REJECTED' => 'rose',
                        'RETURNED' => 'amber',
                        default => 'slate'
                    }
                ]);
            }
        }
        
        // 3. Downloads
        foreach ($po->downloadLogs as $log) {
            $activities->push((object)[
                'type' => 'download',
                'date' => $log->created_at,
                'user' => $log->user->name,
                'label' => 'Downloaded Document',
                'icon' => 'bi-cloud-download',
                'color' => 'blue'
            ]);
        }
        
        return $activities->sortByDesc('date');
    }

    public function getRevisionsProperty()
    {
        return PurchaseOrder::where('parent_po_number', $this->purchaseOrder->po_number)->get();
    }

    public function getFilesProperty()
    {
        return File::where('doc_id', $this->purchaseOrder->po_number)->get();
    }

    public function approve()
    {
        $this->isProcessing = true;
        
        try {
            ProcessPurchaseOrderApprovalJob::dispatch($this->purchaseOrder, Auth::id(), 'Signed and approved via Full Page View');
            $this->dispatch('toast', message: 'Approval process started in the background...', type: 'info');
        } catch (\Exception $e) {
            Log::error('PurchaseOrderShow approval dispatch failed', [
                'id' => $this->purchaseOrderId, 
                'error' => $e->getMessage()
            ]);
            $this->dispatch('toast', message: 'Failed to start approval: ' . $e->getMessage(), type: 'error');
            $this->isProcessing = false;
        }
    }

    public function checkProcessingStatus()
    {
        if (!$this->isProcessing) return;

        $this->purchaseOrder->refresh();
        
        // If status has changed from IN_REVIEW, it's done
        if ($this->purchaseOrder->workflow_status !== 'IN_REVIEW') {
            $this->isProcessing = false;
            $this->dispatch('toast', message: 'Purchase order processed successfully!', type: 'success');
        }
    }

    public function reject()
    {
        $this->validate([
            'reason' => 'required|min:3|max:500'
        ]);

        $this->isProcessing = true;

        try {
            ProcessPurchaseOrderRejectionJob::dispatch($this->purchaseOrder, Auth::id(), $this->reason);
            
            $this->dispatch('toast', message: 'Rejection process started...', type: 'info');
            $this->reason = '';
            $this->dispatch('close-reject-modal');
        } catch (\Exception $e) {
            Log::error('PurchaseOrderShow rejection dispatch failed', [
                'id' => $this->purchaseOrderId, 
                'error' => $e->getMessage()
            ]);
            $this->dispatch('toast', message: 'Failed to start rejection: ' . $e->getMessage(), type: 'error');
            $this->isProcessing = false;
        }
    }

    public function render()
    {
        return view('livewire.purchase-order.purchase-order-show', [
            'purchaseOrder' => $this->purchaseOrder,
            'activities' => $this->activities,
            'revisions' => $this->revisions,
            'files' => $this->files,
            'director' => Auth::user()->hasRole('director')
        ])->layout('new.layouts.app');
    }
}
