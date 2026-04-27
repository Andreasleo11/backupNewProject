<?php

namespace App\Livewire;

use Livewire\Component;

class TopVendorsModal extends Component
{
    public $showModal = false;

    public $topVendors = [];

    protected $listeners = ['showTopVendors' => 'openModal'];

    public function mount()
    {
        // Load top vendors from parent component
        // This will be passed from the dashboard
    }

    public function openModal()
    {
        // Get top vendors from parent dashboard component
        $dashboard = app(\App\Livewire\PurchaseOrderDashboard::class);
        $this->topVendors = $dashboard->topVendors ?? [];
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
        $this->dispatch('getVendorDetails', $vendorName);
    }

    public function render()
    {
        return view('livewire.purchase-order.top-vendors-modal');
    }
}
