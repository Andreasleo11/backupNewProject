use Livewire\Component;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Log;

class PurchaseOrderDashboard extends Component
{
    public $selectedMonth;
    public $monthlyTotals = [];
    public $topVendors = [];
    public $vendorTotals = [];
    public $availableMonths = [];
    public $statusCounts = [
        'approved' => 0,
        'waiting' => 0,
        'rejected' => 0,
        'canceled' => 0,
    ];
    public $categoryChartData = [];

    protected $listeners = ['refreshDashboard' => '$refresh'];

    public function mount()
    {
        $this->selectedMonth = now()->format('Y-m');
        $this->loadDashboardData();
    }

    public function updatedSelectedMonth()
    {
        $this->loadDashboardData();
        $this->emit('monthChanged', $this->selectedMonth);
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
                'error' => $e->getMessage()
            ]);

            // Set default empty data
            $this->monthlyTotals = [];
            $this->topVendors = [];
            $this->vendorTotals = [];
            $this->categoryChartData = [];
        }
    }

    public function getVendorDetails($vendorName)
    {
        try {
            $poService = app(PurchaseOrderService::class);
            $details = $poService->getVendorDetails($vendorName, $this->selectedMonth);

            $this->emit('showVendorDetails', $details);

        } catch (\Exception $e) {
            Log::error('Failed to get vendor details', [
                'vendor' => $vendorName,
                'month' => $this->selectedMonth,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function refreshData()
    {
        $this->loadDashboardData();
        $this->emit('dataRefreshed');
    }

    public function render()
    {
        return view('livewire.purchase-order.dashboard');
    }
}