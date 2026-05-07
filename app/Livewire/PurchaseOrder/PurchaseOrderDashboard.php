<?php

namespace App\Livewire\PurchaseOrder;

use App\Services\PurchaseOrderAnalyticsService;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PurchaseOrderDashboard extends Component
{
    public $selectedDateRange = 'last_30_days';

    public $selectedStatuses = [];

    public $selectedCategory = '';

    public $selectedMonth;

    public $monthlyTotals;

    public $topVendors;

    public $vendorTotals;

    public $availableMonths;

    public $statusCounts;

    public $categoryChartData;

    public $operationalMetrics = [];

    public $availableCategories = [];

    public $showVendorModal = false;

    public $selectedVendorDetails = [];

    public $selectedVendorName = '';

    public $showTopVendorsModal = false;

    protected $listeners = ['refreshDashboard' => '$refresh'];

    public function mount()
    {
        $this->selectedMonth = now()->format('Y-m');
        $this->loadDashboardData();
        $this->loadAvailableCategories();
    }

    public function loadAvailableCategories()
    {
        $this->availableCategories = \App\Models\PurchaseOrderCategory::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->toArray();
    }

    public function updatedSelectedDateRange()
    {
        Log::info('Date range changed to: ' . $this->selectedDateRange);
        $this->loadDashboardData();
        $this->dispatchDataUpdate();
    }

    public function updatedSelectedStatuses()
    {
        Log::info('Status filters changed: ' . json_encode($this->selectedStatuses));
        $this->loadDashboardData();
        $this->dispatchDataUpdate();
    }

    public function updatedSelectedCategory()
    {
        Log::info('Category filter changed: ' . $this->selectedCategory);
        $this->loadDashboardData();
        $this->dispatchDataUpdate();
    }

    public function updatedSelectedMonth()
    {
        Log::info('Month changed to: ' . $this->selectedMonth);
        $this->loadDashboardData();
        $this->dispatchDataUpdate();
    }

    private function dispatchDataUpdate()
    {
        $this->dispatch('dashboardDataUpdated', [
            'monthlyTotals' => $this->monthlyTotals->toArray(),
            'statusCounts' => $this->statusCounts,
            'categoryChartData' => $this->categoryChartData->toArray(),
            'operationalMetrics' => $this->operationalMetrics,
        ]);
        Log::info('Dashboard data update dispatched');
    }

    public function loadDashboardData()
    {
        try {
            $analyticsService = app(PurchaseOrderAnalyticsService::class);
            $poService = app(PurchaseOrderService::class);

            // Build filter parameters
            $filters = $this->buildFilters();

            // Get operational metrics for command center
            $operationalMetrics = $analyticsService->getOperationalMetrics($filters);

            // Get legacy dashboard data with filters
            $dashboardData = $poService->getDashboardData($this->selectedMonth);

            // Combine data for comprehensive dashboard
            $this->monthlyTotals = $dashboardData['monthlyTotals'];
            $this->topVendors = $dashboardData['topVendors'];
            $this->vendorTotals = $dashboardData['vendorTotals'];
            $this->availableMonths = $dashboardData['availableMonths'];
            $this->statusCounts = $dashboardData['statusCounts'];
            $this->categoryChartData = $dashboardData['categoryChartData'];

            // Add operational metrics
            $this->operationalMetrics = $operationalMetrics;

        } catch (\Exception $e) {
            Log::error('Failed to load dashboard data', [
                'filters' => $this->buildFilters(),
                'error' => $e->getMessage(),
            ]);

            // Set default empty data
            $this->monthlyTotals = collect();
            $this->topVendors = collect();
            $this->vendorTotals = collect();
            $this->availableMonths = collect();
            $this->categoryChartData = collect();
            $this->operationalMetrics = [];
        }
    }

    private function buildFilters(): array
    {
        $dateRange = match ($this->selectedDateRange) {
            'last_7_days' => [now()->subDays(7), now()],
            'last_30_days' => [now()->subDays(30), now()],
            'last_90_days' => [now()->subDays(90), now()],
            'last_6_months' => [now()->subMonths(6), now()],
            'last_year' => [now()->subYear(), now()],
            default => [now()->subDays(30), now()],
        };

        return [
            'date_range' => $dateRange,
            'statuses' => $this->selectedStatuses,
            'categories' => $this->selectedCategory ? [$this->selectedCategory] : [],
        ];
    }

    public function getVendorDetails($vendorName)
    {
        try {
            $poService = app(PurchaseOrderService::class);
            $details = $poService->getVendorDetails($vendorName, $this->selectedMonth);

            $this->selectedVendorName = $vendorName;
            $this->selectedVendorDetails = $details->toArray();
            $this->showVendorModal = true;

            $this->dispatch('showVendorDetails', $vendorName, $this->selectedVendorDetails);

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

    public function showTopVendors()
    {
        $this->showTopVendorsModal = true;
        $this->dispatch('showTopVendors', $this->topVendors->toArray());
    }

    public function closeTopVendorsModal()
    {
        $this->showTopVendorsModal = false;
    }

    public function showCreateModal()
    {
        return redirect()->route('po.create');
    }

    public function showBulkActions()
    {
        // TODO: Implement bulk actions modal
        session()->flash('info', 'Bulk actions feature coming soon!');
    }

    public function exportDashboard()
    {
        try {
            $filters = $this->buildFilters();
            $analyticsService = app(PurchaseOrderAnalyticsService::class);

            // Generate comprehensive report
            $reportData = [
                'summary' => $analyticsService->getOperationalMetrics($filters),
                'generated_at' => now(),
                'filters_applied' => $filters,
            ];

            // For now, just flash a message - in production this would generate a PDF/Excel
            session()->flash('success', 'Dashboard export feature coming soon! Data prepared for export.');

            Log::info('Dashboard export requested', [
                'user_id' => auth()->id(),
                'filters' => $filters,
            ]);

        } catch (\Exception $e) {
            Log::error('Dashboard export failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Failed to export dashboard data.');
        }
    }

    public function refreshData()
    {
        Log::info('Refresh data requested');
        $this->loadDashboardData();
        Log::info('Dashboard data refreshed', [
            'monthlyTotalsCount' => $this->monthlyTotals->count(),
            'statusCounts' => $this->statusCounts,
            'categoryChartDataCount' => $this->categoryChartData->count(),
        ]);
        $this->dispatch('dataRefreshed', [
            'monthlyTotals' => $this->monthlyTotals->toArray(),
            'statusCounts' => $this->statusCounts,
            'categoryChartData' => $this->categoryChartData->toArray(),
            'operationalMetrics' => $this->operationalMetrics,
        ]);
        Log::info('dataRefreshed event dispatched');
    }

    public function render()
    {
        return view('livewire.purchase-order.dashboard');
    }
}
