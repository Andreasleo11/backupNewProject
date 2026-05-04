<?php

namespace App\Livewire\PurchaseOrder;

use App\Livewire\PurchaseOrderDashboard;
use Livewire\Component;

class TopVendorsModal extends Component
{
    public $showModal = false;

    public $topVendors = [];

    protected $listeners = ['showTopVendors' => 'openModal'];

    public function openModal($topVendors = [])
    {
        $this->topVendors = $topVendors;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->topVendors = [];
    }

    public function viewVendorDetails($vendorName)
    {
        $this->closeModal();
        // Dispatch to the dashboard component to get vendor details
        $this->dispatch('getVendorDetails', $vendorName)->to(PurchaseOrderDashboard::class);
    }

    public function render()
    {
        return view('livewire.purchase-order.top-vendors-modal');
    }
}
