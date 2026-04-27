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
                <button wire:click="getVendorDetails('{{ $topVendors->first()->vendor_name ?? '' }}')"
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
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Pass PHP data to JavaScript safely
    const monthlyTotalsData = @js($monthlyTotals ?? collect());
    const statusCountsData = @js($statusCounts ?? ['approved' => 0, 'waiting' => 0, 'rejected' => 0, 'canceled' => 0]);
    const categoryChartData = @js($categoryChartData ?? collect());

        document.addEventListener('livewire:init', () => {
            let monthlyChart = null;
            let statusChart = null;
            let categoryChart = null;

            function updateCharts() {
                // Monthly totals chart
                const monthlyCtx = document.getElementById('monthlyChart');
                if (monthlyCtx) {
                    if (monthlyChart) monthlyChart.destroy();

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

                // Status chart
                const statusCtx = document.getElementById('statusChart');
                if (statusCtx) {
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

                // Category chart
                const categoryCtx = document.getElementById('categoryChart');
                if (categoryCtx) {
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
            }

            // Initial chart render
            updateCharts();

            // Listen for Livewire updates
            Livewire.on('monthChanged', updateCharts);
            Livewire.on('dataRefreshed', updateCharts);
        });
    </script>
@endpush