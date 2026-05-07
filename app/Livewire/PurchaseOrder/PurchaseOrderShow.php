<?php

namespace App\Livewire\PurchaseOrder;

use App\Application\Approval\Contracts\Approvals;
use App\Models\File;
use App\Models\PurchaseOrder;
use App\Services\PdfProcessingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PurchaseOrderShow extends Component
{
    public $purchaseOrderId;

    public $reason = '';

    public $loading = false;

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
            'latestDownloadLog.user',
        ])->findOrFail($this->purchaseOrderId);
    }

    public function getActivitiesProperty()
    {
        $po = $this->purchaseOrder;
        $activities = collect();

        // 1. Initial Submission
        if ($po->approvalRequest && $po->approvalRequest->submitted_at) {
            $activities->push((object) [
                'type' => 'submission',
                'date' => $po->approvalRequest->submitted_at,
                'user' => $po->user->name,
                'label' => 'Submitted for Approval',
                'icon' => 'bi-send',
                'color' => 'indigo',
            ]);
        }

        // 2. Approval Actions
        if ($po->approvalRequest) {
            foreach ($po->approvalRequest->actions as $action) {
                $activities->push((object) [
                    'type' => 'approval',
                    'date' => $action->created_at,
                    'user' => $action->causer->name ?? 'System',
                    'label' => 'Status: ' . $action->to_status,
                    'remarks' => $action->remarks,
                    'icon' => match ($action->to_status) {
                        'APPROVED' => 'bi-check-circle',
                        'REJECTED' => 'bi-x-circle',
                        'RETURNED' => 'bi-arrow-left-right',
                        default => 'bi-info-circle'
                    },
                    'color' => match ($action->to_status) {
                        'APPROVED' => 'emerald',
                        'REJECTED' => 'rose',
                        'RETURNED' => 'amber',
                        default => 'slate'
                    },
                ]);
            }
        }

        // 3. Downloads
        foreach ($po->downloadLogs as $log) {
            $activities->push((object) [
                'type' => 'download',
                'date' => $log->created_at,
                'user' => $log->user->name,
                'label' => 'Downloaded Document',
                'icon' => 'bi-cloud-download',
                'color' => 'blue',
            ]);
        }

        // 4. Model System Logs (PO & Invoice)
        $poLogs = \Spatie\Activitylog\Models\Activity::where('subject_type', \App\Models\PurchaseOrder::class)
            ->where('subject_id', $po->id)
            ->with('causer')
            ->get()
            ->map(function ($log) {
                return (object) [
                    'type' => 'system',
                    'date' => $log->created_at,
                    'user' => $log->causer->name ?? 'System',
                    'label' => 'Purchase Order ' . ucfirst($log->description),
                    'icon' => 'bi-journal-text',
                    'color' => 'gray',
                ];
            });

        $invoiceIds = $po->invoices()->pluck('id');
        $invoiceLogs = collect();

        if ($invoiceIds->isNotEmpty()) {
            $invoiceLogs = \Spatie\Activitylog\Models\Activity::where('subject_type', \App\Models\Invoice::class)
                ->whereIn('subject_id', $invoiceIds)
                ->with('causer')
                ->get()
                ->map(function ($log) {
                    return (object) [
                        'type' => 'system',
                        'date' => $log->created_at,
                        'user' => $log->causer->name ?? 'System',
                        'label' => 'Invoice ' . ucfirst($log->description),
                        'icon' => 'bi-receipt',
                        'color' => 'gray',
                    ];
                });
        }

        return $activities->concat($poLogs)->concat($invoiceLogs)->sortByDesc('date');
    }

    public function getRevisionsProperty()
    {
        return PurchaseOrder::where('parent_po_number', $this->purchaseOrder->po_number)->get();
    }

    public function getFilesProperty()
    {
        return File::where('doc_id', $this->purchaseOrder->po_number)->get();
    }

    public function approve(Approvals $approvals, PdfProcessingService $pdfService)
    {
        $this->authorize('approve', $this->purchaseOrder);
        $this->loading = true;

        try {
            DB::transaction(function () use ($approvals, $pdfService) {
                $po = $this->purchaseOrder;
                $userId = Auth::id();

                // 1. Process approval workflow
                $approvals->approve($po, $userId, 'Signed and approved via Full Page View');

                // 2. Process PDF signature
                $pdfService->sign($po, $userId);

                // 3. Update approval date
                $po->update(['approved_date' => now()]);
            });

            $this->purchaseOrder->refresh();

            $this->dispatch('flash', message: 'Purchase order signed and approved successfully!', type: 'success');
        } catch (\Exception $e) {
            Log::error('PurchaseOrderShow approval failed', [
                'id' => $this->purchaseOrderId,
                'error' => $e->getMessage(),
            ]);
            $this->dispatch('toast', message: 'Approval failed: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->loading = false;
        }
    }

    public function reject(Approvals $approvals)
    {
        $this->authorize('reject', $this->purchaseOrder);
        $this->validate([
            'reason' => 'required|min:3|max:500',
        ]);

        $this->loading = true;

        try {
            $approvals->reject($this->purchaseOrder, Auth::id(), $this->reason);

            $this->purchaseOrder->refresh();

            $this->dispatch('flash', message: 'Purchase order rejected.', type: 'success');
            $this->reason = '';
            $this->dispatch('close-reject-modal');
        } catch (\Exception $e) {
            Log::error('PurchaseOrderShow rejection failed', [
                'id' => $this->purchaseOrderId,
                'error' => $e->getMessage(),
            ]);
            $this->dispatch('toast', message: 'Rejection failed: ' . $e->getMessage(), type: 'error');
        } finally {
            $this->loading = false;
        }
    }

    public function render()
    {
        return view('livewire.purchase-order.purchase-order-show', [
            'purchaseOrder' => $this->purchaseOrder,
            'activities' => $this->activities,
            'revisions' => $this->revisions,
            'files' => $this->files,
            'director' => Auth::user()->hasRole('director'),
        ])->layout('new.layouts.app');
    }
}
