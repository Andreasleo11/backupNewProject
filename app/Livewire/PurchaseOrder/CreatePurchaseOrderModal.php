<?php

namespace App\Livewire\PurchaseOrder;

use App\Services\PdfProcessingService;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreatePurchaseOrderModal extends Component
{
    use WithFileUploads;

    public $showModal = false;

    // Form fields
    public $po_number;

    public $vendor_name;

    public $invoice_date;

    public $invoice_number;

    public $currency = 'IDR';

    public $total;

    public $purchase_order_category_id;

    public $tanggal_pembayaran;

    public $pdf_file;

    // Form validation
    public $vendors = [];

    public $categories = [];

    protected $listeners = ['openCreateModal' => 'openModal'];

    protected $rules = [
        'po_number' => 'required|string|max:50|unique:purchase_orders,po_number',
        'vendor_name' => 'required|string|max:255',
        'invoice_date' => 'required|date|before_or_equal:today',
        'invoice_number' => 'required|string|max:100',
        'currency' => 'required|string|size:3',
        'total' => 'required|numeric|min:0',
        'purchase_order_category_id' => 'required|exists:purchase_order_categories,id',
        'tanggal_pembayaran' => 'required|date|after:invoice_date',
        'pdf_file' => 'required|file|mimes:pdf|max:5120', // 5MB max
    ];

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

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function resetForm()
    {
        $this->po_number = null;
        $this->vendor_name = null;
        $this->invoice_date = now()->format('Y-m-d');
        $this->invoice_number = null;
        $this->currency = 'IDR';
        $this->total = null;
        $this->purchase_order_category_id = null;
        $this->tanggal_pembayaran = null;
        $this->pdf_file = null;
    }

    public function updatedTotal($value)
    {
        // Remove commas from total input
        $this->total = str_replace(',', '', $value);
    }

    public function save()
    {
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

            // Process and store the PDF file
            $pdfService = app(PdfProcessingService::class);
            $pdfService->validatePdfFile($this->pdf_file);

            // Generate filename and store
            $poNumber = is_int($this->po_number) ? $this->po_number : intval($this->po_number);
            $filename = $pdfService->storePdfFile($this->pdf_file, $poNumber);

            // Prepare data for service
            $data = [
                'po_number' => $poNumber,
                'vendor_name' => $this->vendor_name,
                'invoice_date' => $invoiceDate,
                'invoice_number' => $this->invoice_number,
                'currency' => $this->currency,
                'total' => floatval($this->total),
                'purchase_order_category_id' => intval($this->purchase_order_category_id),
                'tanggal_pembayaran' => $this->tanggal_pembayaran,
                'pdf_file' => $filename,
            ];

            // Create PO using service
            $poService = app(PurchaseOrderService::class);
            $purchaseOrder = $poService->create($data);

            // Dispatch success event
            $this->dispatch('poCreated', [
                'po' => $purchaseOrder,
                'message' => 'Purchase Order created successfully!',
            ]);

            // Close modal and reset
            $this->closeModal();

            // Refresh parent component
            $this->dispatch('refreshDashboard');

        } catch (\Exception $e) {
            Log::error('Failed to create PO via modal', [
                'data' => $this->all(),
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            $this->addError('general', 'Failed to create purchase order: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.purchase-order.create-purchase-order-modal');
    }
}
