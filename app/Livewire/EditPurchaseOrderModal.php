<?php

namespace App\Livewire;

use App\Services\PdfProcessingService;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditPurchaseOrderModal extends Component
{
    use WithFileUploads;

    public $showModal = false;

    public $purchaseOrderId;

    public $purchaseOrder;

    // Form fields
    public $po_number;

    public $vendor_name;

    public $invoice_date;

    public $invoice_number;

    public $currency;

    public $total;

    public $purchase_order_category_id;

    public $tanggal_pembayaran;

    public $pdf_file;

    // Form validation
    public $vendors = [];

    public $categories = [];

    protected $listeners = ['openEditModal' => 'openModal'];

    protected function rules()
    {
        return [
            'po_number' => 'required|string|max:50|unique:purchase_orders,po_number,' . $this->purchaseOrderId,
            'vendor_name' => 'required|string|max:255',
            'invoice_date' => 'required|date|before_or_equal:today',
            'invoice_number' => 'required|string|max:100',
            'currency' => 'required|string|size:3',
            'total' => 'required|numeric|min:0',
            'purchase_order_category_id' => 'required|exists:purchase_order_categories,id',
            'tanggal_pembayaran' => 'required|date|after:invoice_date',
            'pdf_file' => 'nullable|file|mimes:pdf|max:5120', // 5MB max, optional for edit
        ];
    }

    protected $validationAttributes = [
        'po_number' => 'PO number',
        'vendor_name' => 'vendor name',
        'invoice_date' => 'invoice date',
        'invoice_number' => 'invoice number',
        'currency' => 'currency',
        'total' => 'total amount',
        'purchase_order_category_id' => 'category',
        'tanggal_pembayaran' => 'payment date',
        'pdf_file' => 'PDF file',
    ];

    public function mount()
    {
        $this->loadFormData();
    }

    public function loadFormData()
    {
        // Load vendors for autocomplete
        $this->vendors = \App\Models\PurchaseOrder::distinct()
            ->pluck('vendor_name')
            ->filter()
            ->values()
            ->toArray();

        // Load categories
        $this->categories = \App\Models\PurchaseOrderCategory::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function openModal($poId)
    {
        $this->purchaseOrderId = $poId;
        $this->loadPurchaseOrder();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function loadPurchaseOrder()
    {
        $this->purchaseOrder = \App\Models\PurchaseOrder::findOrFail($this->purchaseOrderId);

        // Populate form fields
        $this->po_number = $this->purchaseOrder->po_number;
        $this->vendor_name = $this->purchaseOrder->vendor_name;
        $this->invoice_date = $this->purchaseOrder->invoice_date;
        $this->invoice_number = $this->purchaseOrder->invoice_number;
        $this->currency = $this->purchaseOrder->currency;
        $this->total = $this->purchaseOrder->total;
        $this->purchase_order_category_id = $this->purchaseOrder->purchase_order_category_id;
        $this->tanggal_pembayaran = $this->purchaseOrder->tanggal_pembayaran;
        // PDF file is optional for edits
    }

    public function updatedTotal($value)
    {
        // Remove commas from total input
        $this->total = str_replace(',', '', $value);
    }

    public function canEdit()
    {
        return $this->purchaseOrder && $this->purchaseOrder->getStatusEnum()->canEdit();
    }

    public function save()
    {
        if (! $this->canEdit()) {
            $this->addError('general', 'This purchase order cannot be edited in its current status.');

            return;
        }

        $this->validate();

        try {
            // Convert invoice_date from dd.mm.yy format if needed
            $invoiceDate = $this->invoice_date;
            if (strpos($invoiceDate, '.') !== false) {
                $date = \DateTime::createFromFormat('d.m.y', $invoiceDate);
                if ($date) {
                    $invoiceDate = $date->format('Y-m-d');
                }
            }

            // Handle PDF file if uploaded
            $filename = $this->purchaseOrder->filename; // Keep existing file by default
            if ($this->pdf_file) {
                $pdfService = app(PdfProcessingService::class);
                $pdfService->validatePdfFile($this->pdf_file);

                // Generate new filename and store
                $poNumber = is_int($this->po_number) ? $this->po_number : intval($this->po_number);
                $filename = $pdfService->storePdfFile($this->pdf_file, $poNumber);
            }

            // Prepare data for service
            $data = [
                'po_number' => is_int($this->po_number) ? $this->po_number : intval($this->po_number),
                'vendor_name' => $this->vendor_name,
                'invoice_date' => $invoiceDate,
                'invoice_number' => $this->invoice_number,
                'currency' => $this->currency,
                'total' => floatval($this->total),
                'purchase_order_category_id' => intval($this->purchase_order_category_id),
                'tanggal_pembayaran' => $this->tanggal_pembayaran,
            ];

            // Only include PDF file if it was uploaded
            if ($this->pdf_file) {
                $data['pdf_file'] = $filename;
            }

            // Update PO using service
            $poService = app(PurchaseOrderService::class);
            $purchaseOrder = $poService->update($this->purchaseOrderId, $data);

            // Dispatch success event
            $this->dispatch('poUpdated', [
                'po' => $purchaseOrder,
                'message' => 'Purchase Order updated successfully!',
            ]);

            // Close modal and reset
            $this->closeModal();

            // Refresh parent component
            $this->dispatch('refreshDashboard');

        } catch (\Exception $e) {
            Log::error('Failed to update PO via modal', [
                'po_id' => $this->purchaseOrderId,
                'data' => $this->all(),
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            $this->addError('general', 'Failed to update purchase order: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.purchase-order.edit-purchase-order-modal');
    }
}
