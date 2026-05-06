<?php

namespace App\Livewire\PurchaseOrder;

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

    public function mount($purchaseOrderId)
    {
        $this->purchaseOrderId = $purchaseOrderId;
        $this->loadInvoices();
    }

    public function loadInvoices()
    {
        $po = PurchaseOrder::with(['invoices.files'])->findOrFail($this->purchaseOrderId);
        $this->invoices = $po->invoices;
        $this->purchaseOrderTotal = $po->total;
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
        $this->resetForm();
        
        // Default to PO currency
        $po = PurchaseOrder::findOrFail($this->purchaseOrderId);
        $this->total_currency = $po->currency ?? 'IDR';
        
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->resetForm();
        
        $invoice = Invoice::findOrFail($id);
        
        $this->invoiceId = $invoice->id;
        $this->invoice_number = $invoice->invoice_number;
        $this->invoice_date = $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : '';
        $this->payment_date = $invoice->payment_date ? $invoice->payment_date->format('Y-m-d') : '';
        $this->total = $invoice->total;
        $this->total_currency = $invoice->total_currency;
        
        $this->showModal = true;
    }

    public function save()
    {
        $validated = $this->validate();
        
        try {
            if ($this->invoiceId) {
                $invoice = Invoice::findOrFail($this->invoiceId);
                $invoice->update($validated);
                $this->dispatch('flash', message: 'Invoice updated successfully.', type: 'success');
            } else {
                $validated['purchase_order_id'] = $this->purchaseOrderId;
                Invoice::create($validated);
                $this->dispatch('flash', message: 'Invoice added successfully.', type: 'success');
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

    public function resetForm()
    {
        $this->invoiceId = null;
        $this->invoice_number = '';
        $this->invoice_date = '';
        $this->payment_date = '';
        $this->total = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.purchase-order.invoice-manager');
    }
}
