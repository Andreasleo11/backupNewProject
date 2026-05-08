<?php

namespace App\Livewire\PurchaseOrder;

use App\Domain\FileCompliance\Services\FileService;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class InvoiceManager extends Component
{
    public $purchaseOrderId;

    public $invoices = [];

    public $purchaseOrderTotal = 0;

    // Modal state
    public $showModal = false;

    public $invoiceId = null;

    // Form fields
    public $invoice_number = '';

    public $invoice_date = '';

    public $payment_date = '';

    public $total = '';

    public $total_currency = 'IDR';

    public $purchaseOrder;

    public $currencyMismatchWarning = false;

    public $showCurrencyDetails = false;

    public $attachmentModalInvoice = null;

    protected $fileService;

    public function boot()
    {
        $this->fileService = app(FileService::class);
    }

    public function mount($purchaseOrderId)
    {
        $this->purchaseOrderId = $purchaseOrderId;
        $this->purchaseOrder = PurchaseOrder::findOrFail($purchaseOrderId);
        $this->loadInvoices();
    }

    public function loadInvoices()
    {
        $this->purchaseOrder->load(['invoices.files']);
        $this->invoices = $this->purchaseOrder->invoices;
        $this->purchaseOrderTotal = $this->purchaseOrder->total;

        // Refresh modal data if modal is open
        if ($this->attachmentModalInvoice) {
            $this->attachmentModalInvoice->load('files');
        }
    }

    public function rules()
    {
        return [
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'payment_date' => 'nullable|date',
            'total' => 'required|numeric|min:0',
            'total_currency' => 'required|string|max:10',
        ];
    }

    public function create()
    {
        $this->authorize('manageInvoices', $this->purchaseOrder);
        $this->resetForm();

        // Default to PO currency
        $this->total_currency = $this->purchaseOrder->currency ?? 'IDR';
        $this->checkCurrencyMismatch();

        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->authorize('manageInvoices', $this->purchaseOrder);
        $this->resetForm();

        $invoice = Invoice::findOrFail($id);

        $this->invoiceId = $invoice->id;
        $this->invoice_number = $invoice->invoice_number;
        $this->invoice_date = $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : '';
        $this->payment_date = $invoice->payment_date ? $invoice->payment_date->format('Y-m-d') : '';
        $this->total = $invoice->total;
        $this->total_currency = $invoice->total_currency;
        $this->checkCurrencyMismatch();

        $this->showModal = true;
    }

    public function save()
    {
        $this->authorize('manageInvoices', $this->purchaseOrder);
        $validated = $this->validate();

        // Check for currency mismatch and add warning to the message
        $currencyWarning = '';
        if ($this->currencyMismatchWarning) {
            $currencyWarning = ' Warning: Invoice currency differs from PO currency. Please ensure proper exchange rate calculations.';
        }

        try {
            if ($this->invoiceId) {
                $invoice = Invoice::findOrFail($this->invoiceId);
                $invoice->update($validated);
                $this->dispatch('flash', message: 'Invoice updated successfully.' . $currencyWarning, type: $currencyWarning ? 'warning' : 'success');
            } else {
                $validated['purchase_order_id'] = $this->purchaseOrderId;
                Invoice::create($validated);
                $this->dispatch('flash', message: 'Invoice added successfully.' . $currencyWarning, type: $currencyWarning ? 'warning' : 'success');
            }

            $this->showModal = false;
            $this->loadInvoices();

            // Dispatch event to parent to refresh the PO total/invoices
            $this->dispatch('po-updated');

        } catch (\Exception $e) {
            Log::error('Failed to save invoice', ['error' => $e->getMessage()]);
            $this->dispatch('toast', message: 'Failed to save invoice.', type: 'error');
        }
    }

    public function delete($id)
    {
        $this->authorize('manageInvoices', $this->purchaseOrder);
        try {
            $invoice = Invoice::findOrFail($id);
            $invoice->delete();

            $this->loadInvoices();
            $this->dispatch('flash', message: 'Invoice deleted successfully.', type: 'success');
            $this->dispatch('po-updated');
        } catch (\Exception $e) {
            Log::error('Failed to delete invoice', ['error' => $e->getMessage()]);
            $this->dispatch('toast', message: 'Failed to delete invoice.', type: 'error');
        }
    }

    public function deleteFile($fileId)
    {
        $this->authorize('manageInvoices', $this->purchaseOrder);
        try {
            $result = $this->fileService->deleteFile($fileId);

            if ($result) {
                $this->loadInvoices(); // Reload to refresh file count

                // If we're in the attachment modal, refresh the modal data
                if ($this->attachmentModalInvoice) {
                    $this->attachmentModalInvoice->load('files');
                    // Close modal if no files left
                    if ($this->attachmentModalInvoice->files->isEmpty()) {
                        $this->closeAttachmentModal();
                    }
                }

                $this->dispatch('flash', message: 'File deleted successfully.', type: 'success');
            } else {
                $this->dispatch('toast', message: 'File not found or could not be deleted.', type: 'error');
            }
        } catch (\Exception $e) {
            Log::error('Failed to delete file', ['error' => $e->getMessage(), 'file_id' => $fileId]);
            $this->dispatch('toast', message: 'Failed to delete file.', type: 'error');
        }
    }

    public function updatedTotalCurrency()
    {
        $this->checkCurrencyMismatch();
    }

    public function checkCurrencyMismatch()
    {
        $this->currencyMismatchWarning = $this->total_currency !== $this->purchaseOrder->currency;
    }

    public function resetForm()
    {
        $this->invoiceId = null;
        $this->invoice_number = '';
        $this->invoice_date = '';
        $this->payment_date = '';
        $this->total = '';
        $this->currencyMismatchWarning = false;
        $this->resetValidation();
    }

    public function openAttachmentModal($invoiceId)
    {
        $this->attachmentModalInvoice = Invoice::with('files')->findOrFail($invoiceId);
    }

    public function closeAttachmentModal()
    {
        $this->attachmentModalInvoice = null;
    }

    public function getHasCurrencyMismatchesProperty()
    {
        return collect($this->invoices)->contains(function ($invoice) {
            return $invoice->total_currency !== $this->purchaseOrder->currency;
        });
    }

    public function getCurrencyInfoProperty()
    {
        $poCurrency = $this->purchaseOrder->currency ?? 'IDR';
        $invoiceCurrencies = collect($this->invoices)->pluck('total_currency')->unique()->values();

        // Separate calculations for matching and mismatched invoices
        $matchingInvoices = collect($this->invoices)->where('total_currency', $poCurrency);
        $mismatchedInvoices = collect($this->invoices)->where('total_currency', '!=', $poCurrency);

        $totalInvoicedMatching = $matchingInvoices->sum('total');
        $totalInvoicedMismatched = $mismatchedInvoices->sum('total');
        $poTotal = $this->purchaseOrderTotal;
        $remaining = max(0, $poTotal - $totalInvoicedMatching);
        $completionPercentage = $poTotal > 0 ? min(100, ($totalInvoicedMatching / $poTotal) * 100) : 0;

        return [
            'po_currency' => $poCurrency,
            'invoice_currencies' => $invoiceCurrencies,
            'has_mismatches' => $this->hasCurrencyMismatches,
            'mixed_currencies' => $invoiceCurrencies->count() > 1 || ($invoiceCurrencies->count() === 1 && !$invoiceCurrencies->contains($poCurrency)),
            'total_invoiced_matching' => $totalInvoicedMatching,
            'total_invoiced_mismatched' => $totalInvoicedMismatched,
            'po_total' => $poTotal,
            'remaining' => $remaining,
            'completion_percentage' => $completionPercentage,
            'has_mismatched_invoices' => $mismatchedInvoices->isNotEmpty()
        ];
    }

    public function render()
    {
        return view('livewire.purchase-order.invoice-manager', [
            'currencyInfo' => $this->currencyInfo
        ]);
    }
}
