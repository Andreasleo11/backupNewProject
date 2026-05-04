<?php

namespace App\Livewire\PurchaseOrder;

use Livewire\Component;

class VendorDetailsModal extends Component
{
    public $showModal = false;

    public $vendorName = '';

    public $vendorDetails = [];

    protected $listeners = ['showVendorDetails' => 'openModal'];

    public function openModal($vendorName, $vendorDetails = [])
    {
        $this->vendorName = $vendorName;
        $this->vendorDetails = $vendorDetails;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->vendorName = '';
        $this->vendorDetails = [];
    }

    public function render()
    {
        return view('livewire.purchase-order.vendor-details-modal');
    }
}
