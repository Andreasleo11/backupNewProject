<?php

namespace App\Livewire\PurchaseOrder;

use App\Services\PdfProcessingService;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditPurchaseOrderForm extends Component
{
    use WithFileUploads;

    public $purchaseOrderId;
    public $purchaseOrder;

    // Form fields
    public $po_number;
    public $vendor_name;
    public $currency;
    public $total;
    public $purchase_order_category_id;
    public $pdf_file;

    // Form validation
    public $vendors = [];
    public $categories = [];

    protected $listeners = ['editModeEntered' => 'loadPurchaseOrder'];

    protected function rules()
    {
        return [
            'po_number' => 'required|numeric|unique:purchase_orders,po_number,' . $this->purchaseOrderId,
            'vendor_name' => 'required|string|max:255',
            'currency' => 'required|string|size:3',
            'total' => 'required|numeric|min:0',
            'purchase_order_category_id' => 'required|exists:purchase_order_categories,id',
            'pdf_file' => 'nullable|file|mimes:pdf|max:5120', // 5MB max, optional for edit
        ];
    }

    protected $validationAttributes = [
        'po_number' => 'PO number',
        'vendor_name' => 'vendor name',
        'currency' => 'currency',
        'total' => 'total amount',
        'purchase_order_category_id' => 'category',
        'pdf_file' => 'PDF file',
    ];

    public function mount($poId = null)
    {
        if ($poId) {
            $this->purchaseOrderId = $poId;
            $this->loadPurchaseOrder();
            $this->authorize('update', $this->purchaseOrder);
        }
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

    public function loadPurchaseOrder()
    {
        if (!$this->purchaseOrderId) return;

        $this->purchaseOrder = \App\Models\PurchaseOrder::findOrFail($this->purchaseOrderId);

        // Populate form fields
        $this->po_number = $this->purchaseOrder->po_number;
        $this->vendor_name = $this->purchaseOrder->vendor_name;
        $this->currency = $this->purchaseOrder->currency;
        $this->total = $this->purchaseOrder->total;
        $this->purchase_order_category_id = $this->purchaseOrder->purchase_order_category_id;
        // PDF file is optional for edits
    }

    public function clearPdfFile()
    {
        $this->pdf_file = null;
    }


    public function getCanEditProperty()
    {
        return $this->purchaseOrder && auth()->user()->can('update', $this->purchaseOrder);
    }

    public function save()
    {
        $this->authorize('update', $this->purchaseOrder);

        if (! $this->canEdit) {
            $this->addError('general', 'This purchase order cannot be edited in its current status.');
            return;
        }

        $this->validate();

        try {
            // Handle PDF file update
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
                'currency' => $this->currency,
                'total' => floatval($this->total),
                'purchase_order_category_id' => intval($this->purchase_order_category_id),
            ];

            // Only include PDF file if it was uploaded
            if ($this->pdf_file) {
                $data['pdf_file'] = $filename;
            }

            // Update PO using service
            $poService = app(PurchaseOrderService::class);
            $purchaseOrder = $poService->update($this->purchaseOrderId, $data);

            // Flash success message and redirect
            session()->flash('success', 'Purchase Order updated successfully!');
            return redirect()->route('po.index');

        } catch (\Exception $e) {
            Log::error('Failed to update PO via full-screen form', [
                'id' => $this->purchaseOrderId,
                'data' => $this->all(),
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            $this->addError('general', 'Failed to update purchase order: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.purchase-order.edit-purchase-order-form');
    }
}