<?php

namespace App\Livewire;

use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PurchaseOrderDashboard extends Component
{
    public $selectedMonth;

    public $monthlyTotals;

    public $topVendors;

    public $vendorTotals;

    public $availableMonths;

    public $statusCounts;

    public $categoryChartData;

    public $showVendorModal = false;

    public $selectedVendorDetails = [];

    public $selectedVendorName = '';

    protected $listeners = ['refreshDashboard' => '$refresh'];

    public function mount()
    {
        $this->selectedMonth = now()->format('Y-m');
        $this->loadDashboardData();
    }

    public function updatedSelectedMonth()
    {
        $this->loadDashboardData();
        $this->dispatch('monthChanged', $this->selectedMonth);
    }

    public function loadDashboardData()
    {
        try {
            $poService = app(PurchaseOrderService::class);

            // Get dashboard analytics data
            $dashboardData = $poService->getDashboardData($this->selectedMonth);

            $this->monthlyTotals = $dashboardData['monthlyTotals'];
            $this->topVendors = $dashboardData['topVendors'];
            $this->vendorTotals = $dashboardData['vendorTotals'];
            $this->availableMonths = $dashboardData['availableMonths'];
            $this->statusCounts = $dashboardData['statusCounts'];
            $this->categoryChartData = $dashboardData['categoryChartData'];

        } catch (\Exception $e) {
            Log::error('Failed to load dashboard data', [
                'month' => $this->selectedMonth,
                'error' => $e->getMessage(),
            ]);

            // Set default empty data
            $this->monthlyTotals = collect();
            $this->topVendors = collect();
            $this->vendorTotals = collect();
            $this->availableMonths = collect();
            $this->categoryChartData = collect();
        }
    }

    public function getVendorDetails($vendorName)
    {
        try {
            $poService = app(PurchaseOrderService::class);
            $details = $poService->getVendorDetails($vendorName, $this->selectedMonth);

            $this->selectedVendorName = $vendorName;
            $this->selectedVendorDetails = $details->toArray();
            $this->showVendorModal = true;

        } catch (\Exception $e) {
            Log::error('Failed to get vendor details', [
                'vendor' => $vendorName,
                'month' => $this->selectedMonth,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function closeVendorModal()
    {
        $this->showVendorModal = false;
        $this->selectedVendorDetails = [];
        $this->selectedVendorName = '';
    }

    public function refreshData()
    {
        $this->loadDashboardData();
        $this->dispatch('dataRefreshed');
    }

    public function render()
    {
        return view('livewire.purchase-order.dashboard');
    }
}
