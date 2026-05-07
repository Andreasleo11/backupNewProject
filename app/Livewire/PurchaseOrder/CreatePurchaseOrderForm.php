<?php

namespace App\Livewire\PurchaseOrder;

use App\Services\PdfProcessingService;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreatePurchaseOrderForm extends Component
{
    use WithFileUploads;

    // Form fields
    public $po_number;
    public $vendor_name;
    public $currency = 'IDR';
    public $total;
    public $purchase_order_category_id;
    public $pdf_file;

    // Form validation
    public $vendors = [];
    public $categories = [];

    protected $listeners = ['createModeEntered' => 'loadFormData'];

    protected $rules = [
        'po_number' => 'required|string|max:50|unique:purchase_orders,po_number',
        'vendor_name' => 'required|string|max:255',
        'currency' => 'required|string|size:3',
        'total' => 'required|numeric|min:0',
        'purchase_order_category_id' => 'required|exists:purchase_order_categories,id',
        'pdf_file' => 'required|file|mimes:pdf|max:5120', // 5MB max
    ];

    protected $validationAttributes = [
        'po_number' => 'PO number',
        'vendor_name' => 'vendor name',
        'currency' => 'currency',
        'total' => 'total amount',
        'purchase_order_category_id' => 'category',
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

    public function resetForm()
    {
        $this->po_number = null;
        $this->vendor_name = null;
        $this->currency = 'IDR';
        $this->total = null;
        $this->purchase_order_category_id = null;
        $this->pdf_file = null;
    }

    public function exitFormMode()
    {
        // Dispatch event to parent to exit form mode
        $this->dispatch('exitFormMode');
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
                'currency' => $this->currency,
                'total' => floatval($this->total),
                'purchase_order_category_id' => intval($this->purchase_order_category_id),
                'pdf_file' => $filename,
            ];

            // Create PO using service
            $poService = app(PurchaseOrderService::class);
            $purchaseOrder = $poService->create($data);

            // Dispatch success event to parent index
            $this->dispatch('handlePoCreated', [
                'po' => $purchaseOrder,
                'message' => 'Purchase Order created successfully!',
            ])->to(PurchaseOrderIndex::class);

            // Reset form
            $this->resetForm();

        } catch (\Exception $e) {
            Log::error('Failed to create PO via full-screen form', [
                'data' => $this->all(),
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            $this->addError('general', 'Failed to create purchase order: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.purchase-order.create-purchase-order-form');
    }
}