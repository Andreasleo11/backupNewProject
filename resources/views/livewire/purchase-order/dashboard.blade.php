<div>
    {{-- Header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-[0.65rem] font-semibold uppercase tracking-[0.2em] text-sky-600">
                Purchasing
            </p>
            <h1 class="mt-1 text-2xl font-semibold text-slate-900">
                Purchase Order Dashboard
            </h1>
            <p class="mt-1 text-sm text-slate-500">
                Monitor spend per month, status mix, categories, and top vendors.
            </p>

            <nav class="mt-2">
                <ol class="flex items-center text-xs text-slate-400 gap-1">
                    <li>
                        <a href="{{ route('po.dashboard') }}" class="hover:text-slate-700 font-medium">
                            Purchase Orders
                        </a>
                    </li>
                    <li>/</li>
                    <li class="text-slate-500 font-medium">Dashboard</li>
                </ol>
            </nav>
        </div>

        <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:items-center">
            {{-- Month filter --}}
            <div class="flex items-center gap-2">
                <label for="monthFilter" class="text-xs font-medium text-slate-600">
                    Period
                </label>
                <select wire:model.live="selectedMonth"
                        class="block rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500">
                    @foreach ($availableMonths as $month)
                        <option value="{{ $month }}">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button wire:click="showTopVendors"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1">
                    Top 5 vendors
                </button>

                <a href="{{ route('po.index') }}"
                   class="inline-flex items-center rounded-lg bg-sky-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1">
                    View PO list
                </a>
            </div>
        </div>
    </div>

    {{-- Operational Command Center --}}
    @if($operationalMetrics)
        {{-- Urgent Alerts --}}
        <x-urgent-alerts :alerts="$operationalMetrics['urgentAlerts'] ?? []" />

        {{-- Core Operational KPIs --}}
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mt-6">
            {{-- Total Spend --}}
            <x-operational-metric-card
                title="Total Spend"
                :value="'IDR ' . number_format($operationalMetrics['totalSpend'] ?? 0, 0, ',', '.')"
                :secondary-value="number_format($operationalMetrics['orderCount'] ?? 0)"
                secondary-label="Orders"
                :change="12.5"
                icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'></path>"
            />

            {{-- Fulfillment Rate --}}
            <x-operational-metric-card
                title="Fulfillment Rate"
                :value="number_format(($operationalMetrics['fulfillmentRate']['rate'] ?? 0), 1) . '%'"
                :secondary-value="number_format($operationalMetrics['fulfillmentRate']['fulfilled'] ?? 0, 0, ',', '.')"
                secondary-label="Fulfilled Value"
                :change="round(($operationalMetrics['fulfillmentRate']['rate'] ?? 0) - 85, 1)"
                :change-type="(($operationalMetrics['fulfillmentRate']['rate'] ?? 0) >= 90) ? 'positive' : 'negative'"
                icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'></path>"
            />

            {{-- Aging Alert --}}
            <x-operational-metric-card
                title="Critical Aging"
                :value="collect($operationalMetrics['agingAnalysis'] ?? [])->where('bucket', '90+ days')->first()['count'] ?? 0"
                secondary-label="POs over 90 days"
                :alert="(collect($operationalMetrics['agingAnalysis'] ?? [])->where('bucket', '90+ days')->first()['count'] ?? 0) > 0"
                alert-message="Immediate attention required"
                icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z'></path>"
            />

            {{-- Approval Efficiency --}}
            <x-operational-metric-card
                title="Approval Time"
                :value="number_format($operationalMetrics['approvalMetrics']['avg_approval_time_hours'] ?? 0, 1) . 'h'"
                :secondary-value="number_format($operationalMetrics['approvalMetrics']['approval_rate'] ?? 0, 1) . '%'"
                secondary-label="Approval Rate"
                :change="round(24 - ($operationalMetrics['approvalMetrics']['avg_approval_time_hours'] ?? 24), 1)"
                :change-type="(($operationalMetrics['approvalMetrics']['avg_approval_time_hours'] ?? 24) <= 24) ? 'positive' : 'negative'"
                trend="vs target (24h)"
                icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'></path>"
            />
        </div>

        {{-- Advanced Analytics Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
            {{-- Aging Analysis --}}
            <x-aging-analysis :aging-data="$operationalMetrics['agingAnalysis'] ?? []" />

            {{-- Supplier Lead Times --}}
            <x-supplier-lead-times :supplier-data="$operationalMetrics['supplierLeadTimes'] ?? []" />

            {{-- Trend Forecast --}}
            <x-trend-forecast :forecast="$operationalMetrics['trendForecast'] ?? []" />
        </div>
    @endif

    {{-- Charts row --}}
    <div class="grid gap-6 lg:grid-cols-4 mt-6">
        {{-- Monthly totals --}}
        <div class="lg:col-span-2 xl:col-span-3 rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-2 px-4 pt-4">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Monthly totals</h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Sum of purchase order amounts per month.
                    </p>
                </div>
                <button wire:click="refreshData"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-2 py-1 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>
            <div class="relative mt-3 px-4 pb-4">
                <canvas id="monthlyChart" class="h-full w-full"></canvas>
            </div>
        </div>

        {{-- Status & category pies --}}
        <div class="space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-900">PO by status</h2>
                <p class="mt-1 text-xs text-slate-500">Count of POs in each status.</p>
                <div class="mt-3">
                    <canvas id="statusChart" class="h-full w-full"></canvas>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-slate-900">PO by category</h2>
                <p class="mt-1 text-xs text-slate-500">Distribution by category.</p>
                <div class="mt-3">
                    <canvas id="categoryChart" class="h-full w-full"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Vendor details table --}}
    <div class="mt-6 rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex items-center justify-between gap-2 px-4 pt-4">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Vendor Performance</h2>
                <p class="mt-1 text-xs text-slate-500">
                    Top vendors by total purchase order value for {{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->format('M Y') }}.
                </p>
            </div>
        </div>

        <div class="px-4 pb-4">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-700">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 font-medium">Vendor</th>
                            <th class="px-4 py-2 font-medium text-right">PO Count</th>
                            <th class="px-4 py-2 font-medium text-right">Total Amount</th>
                            <th class="px-4 py-2 font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vendorTotals as $vendor)
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-2 font-medium">{{ $vendor['vendor_name'] }}</td>
                                <td class="px-4 py-2 text-right">{{ number_format($vendor['po_count']) }}</td>
                                <td class="px-4 py-2 text-right font-mono">{{ number_format($vendor['total'], 0, ',', '.') }}</td>
                                <td class="px-4 py-2">
                                    <button wire:click="getVendorDetails('{{ $vendor['vendor_name'] }}')"
                                            class="text-sky-600 hover:text-sky-800 text-sm font-medium">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                                    No vendor data available for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    {{-- Modals --}}
    <livewire:purchase-order.create-purchase-order-modal />
    <livewire:purchase-order.top-vendors-modal :show-modal="$showTopVendorsModal" />
    <livewire:purchase-order.vendor-details-modal :show-modal="$showVendorModal" />
    <livewire:purchase-order.purchase-order-detail />
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Pass PHP data to JavaScript safely
        let monthlyTotalsData = @js($monthlyTotals ?? collect());
        let statusCountsData = @js($statusCounts ?? ['approved' => 0, 'waiting' => 0, 'rejected' => 0, 'canceled' => 0]);
        let categoryChartData = @js($categoryChartData ?? collect());

        document.addEventListener('livewire:init', () => {
            let monthlyChart = null;
            let statusChart = null;
            let categoryChart = null;
            let isInitialized = false;

            function createMonthlyChart() {
                console.log('Creating monthly chart');
                const monthlyCtx = document.getElementById('monthlyChart');
                if (!monthlyCtx) {
                    console.log('Monthly chart canvas not found');
                    return;
                }

                if (monthlyChart) {
                    console.log('Destroying existing monthly chart');
                    monthlyChart.destroy();
                }

                const hasMonthlyData = monthlyTotalsData && monthlyTotalsData.length > 0;

                monthlyChart = new Chart(monthlyCtx, {
                    type: 'line',
                    data: {
                        labels: hasMonthlyData ? monthlyTotalsData.map(item => {
                            const date = new Date(item.month + '-01');
                            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                        }) : [],
                        datasets: [{
                            label: 'Total Amount',
                            data: hasMonthlyData ? monthlyTotalsData.map(item => item.total) : [],
                            borderColor: 'rgb(14, 165, 233)',
                            backgroundColor: 'rgba(14, 165, 233, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return 'IDR ' + new Intl.NumberFormat('id-ID').format(value);
                                    }
                                }
                            }
                        }
                    }
                });
            }

            function createStatusChart() {
                const statusCtx = document.getElementById('statusChart');
                if (!statusCtx) return;

                if (statusChart) statusChart.destroy();

                statusChart = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Approved', 'Waiting', 'Rejected', 'Canceled'],
                        datasets: [{
                            data: [
                                statusCountsData.approved || 0,
                                statusCountsData.waiting || 0,
                                statusCountsData.rejected || 0,
                                statusCountsData.canceled || 0
                            ],
                            backgroundColor: [
                                'rgb(34, 197, 94)',  // green
                                'rgb(251, 191, 36)', // yellow
                                'rgb(239, 68, 68)',  // red
                                'rgb(156, 163, 175)' // gray
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }

            function createCategoryChart() {
                const categoryCtx = document.getElementById('categoryChart');
                if (!categoryCtx) return;

                if (categoryChart) categoryChart.destroy();

                const hasCategoryData = categoryChartData && categoryChartData.length > 0;

                categoryChart = new Chart(categoryCtx, {
                    type: 'pie',
                    data: {
                        labels: hasCategoryData ? categoryChartData.map(item => item.label) : [],
                        datasets: [{
                            data: hasCategoryData ? categoryChartData.map(item => item.count) : [],
                            backgroundColor: [
                                'rgb(14, 165, 233)',   // blue
                                'rgb(168, 85, 247)',   // violet
                                'rgb(236, 72, 153)',   // pink
                                'rgb(34, 197, 94)',    // green
                                'rgb(245, 158, 11)'    // amber
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }

            function updateCharts() {
                console.log('updateCharts called');
                createMonthlyChart();
                createStatusChart();
                createCategoryChart();
                console.log('Charts updated');
            }

            // Initial chart render
            updateCharts();
            isInitialized = true;
            console.log('Dashboard initialization complete');

            // Listen for Livewire updates - update charts and operational metrics
            Livewire.on('monthChanged', (data) => {
                console.log('🔄 monthChanged event received:', data);
                if (isInitialized) {
                    if (data && Object.keys(data).length > 0) {
                        console.log('📊 Processing monthChanged data...');
                        // Handle arrays from PHP
                        monthlyTotalsData = Array.isArray(data.monthlyTotals) ? data.monthlyTotals : [];
                        statusCountsData = data.statusCounts || {approved: 0, waiting: 0, rejected: 0, canceled: 0};
                        categoryChartData = Array.isArray(data.categoryChartData) ? data.categoryChartData : [];

                        // Update operational metrics for real-time dashboard
                        if (data.operationalMetrics) {
                            console.log('🎯 Operational metrics updated:', data.operationalMetrics);
                            updateOperationalMetrics(data.operationalMetrics);
                        }

                        console.log('✅ Updated dashboard data:', {
                            monthlyTotalsData: monthlyTotalsData.length,
                            statusCountsData,
                            categoryChartData: categoryChartData.length,
                            hasOperationalMetrics: !!data.operationalMetrics
                        });
                        updateCharts();
                    } else {
                        console.log('⚠️ monthChanged event received but no data');
                    }
                } else {
                    console.log('⏳ monthChanged event ignored - not initialized yet');
                }
            });

            Livewire.on('dataRefreshed', (data) => {
                console.log('🔄 dataRefreshed event received:', data);
                if (isInitialized) {
                    if (data && Object.keys(data).length > 0) {
                        console.log('📊 Processing dataRefreshed data...');
                        // Handle arrays from PHP
                        monthlyTotalsData = Array.isArray(data.monthlyTotals) ? data.monthlyTotals : [];
                        statusCountsData = data.statusCounts || {approved: 0, waiting: 0, rejected: 0, canceled: 0};
                        categoryChartData = Array.isArray(data.categoryChartData) ? data.categoryChartData : [];

                        // Update operational metrics for real-time dashboard
                        if (data.operationalMetrics) {
                            console.log('🎯 Operational metrics refreshed:', data.operationalMetrics);
                            updateOperationalMetrics(data.operationalMetrics);
                        }

                        console.log('✅ Refreshed dashboard data:', {
                            monthlyTotalsData: monthlyTotalsData.length,
                            statusCountsData,
                            categoryChartData: categoryChartData.length,
                            hasOperationalMetrics: !!data.operationalMetrics
                        });
                        updateCharts();
                    } else {
                        console.log('⚠️ dataRefreshed event received but no data');
                    }
                } else {
                    console.log('⏳ dataRefreshed event ignored - not initialized yet');
                }
            });

            function updateOperationalMetrics(metrics) {
                // Update real-time KPI displays
                console.log('🔄 Updating operational metrics displays...');

                // Update fulfillment rate
                const fulfillmentRate = metrics.fulfillmentRate?.rate || 0;
                updateMetricDisplay('fulfillment-rate', fulfillmentRate.toFixed(1) + '%');

                // Update critical aging count
                const criticalAging = metrics.agingAnalysis?.find(item => item.bucket === '90+ days')?.count || 0;
                updateMetricDisplay('critical-aging', criticalAging.toString());

                // Update approval time
                const avgApprovalTime = metrics.approvalMetrics?.avg_approval_time_hours || 0;
                updateMetricDisplay('approval-time', avgApprovalTime.toFixed(1) + 'h');

                // Trigger visual alerts for critical metrics
                if (criticalAging > 0) {
                    triggerAlert('aging-alert', `Critical: ${criticalAging} POs over 90 days`);
                }
                if (fulfillmentRate < 80) {
                    triggerAlert('fulfillment-alert', `Low fulfillment rate: ${fulfillmentRate.toFixed(1)}%`);
                }
            }

            function updateMetricDisplay(metricId, value) {
                const element = document.querySelector(`[data-metric="${metricId}"]`);
                if (element) {
                    element.textContent = value;
                }
            }

            function triggerAlert(alertType, message) {
                // Could integrate with notification system
                console.warn(`🚨 ${alertType}: ${message}`);
            }
        });
    </script>
@endpush
