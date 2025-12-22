@extends('new.layouts.app')

@section('content')
    <div
        x-data="poDashboard({
            initialMonth: '{{ $selectedMonth }}',
            filterUrl: '{{ url('/purchase-orders/filter') }}',
            vendorMonthlyUrl: '{{ url('/purchase-orders/vendor-monthly-totals') }}',
            vendorDetailsUrl: '{{ url('/purchase-orders/vendor-details') }}',
            initialStatusCounts: @js($statusCounts),
            initialCategoryData: @js($categoryChartData),
            initialVendorTotals: @js($vendorTotals),
            initialTopVendors: @js($topVendors),
        })"
        x-init="init()"
        class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6"
    >
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
                            <a href="{{ route('po.dashboard') }}"
                               class="hover:text-slate-600 font-medium">
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
                    <select
                        id="monthFilter"
                        x-model="month"
                        @change="onMonthChange"
                        class="block rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500"
                    >
                        @foreach ($availableMonths as $month)
                            <option value="{{ $month }}">
                                {{ $month }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button
                        type="button"
                        @click="openTopVendors"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1"
                    >
                        Top 5 vendors
                    </button>

                    <form x-ref="detailForm" method="GET" action="{{ route('po.index') }}">
                        <input type="hidden" name="month" :value="month">
                        <button
                            type="button"
                            @click.prevent="$refs.detailForm.submit()"
                            class="inline-flex items-center rounded-lg bg-sky-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1"
                        >
                            View PO list
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Charts row --}}
        <div class="grid gap-6 lg:grid-cols-4">
            {{-- Monthly totals --}}
            <div class="lg:col-span-2 xl:col-span-3 rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-2 px-4 pt-4">
                    <div>
                        <h2 class="text-sm font-semibold text-slate-900">Monthly totals</h2>
                        <p class="mt-1 text-xs text-slate-500">
                            Sum of purchase order amounts per month.
                        </p>
                    </div>
                </div>
                <div class="relative mt-3 px-4 pb-4">
                    <canvas x-ref="monthlyChart" class="h-full w-full"></canvas>
                    <div
                        x-show="loading"
                        class="absolute inset-0 flex items-center justify-center rounded-2xl bg-white/70"
                    >
                        <div class="h-6 w-6 animate-spin rounded-full border-2 border-sky-500 border-t-transparent"></div>
                    </div>
                </div>
            </div>

            {{-- Status & category pies --}}
            <div class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h2 class="text-sm font-semibold text-slate-900">PO by status</h2>
                    <p class="mt-1 text-xs text-slate-500">Count of POs in each status.</p>
                    <div class="mt-3">
                        <canvas x-ref="statusChart" class="h-full w-full"></canvas>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <h2 class="text-sm font-semibold text-slate-900">PO by category</h2>
                    <p class="mt-1 text-xs text-slate-500">Distribution across purchasing categories.</p>
                    <div class="mt-3">
                        <canvas x-ref="categoryChart" class="h-full w-full"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Vendors table --}}
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3 md:flex md:items-center md:justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Vendors</h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Total spend per vendor for the selected month.
                    </p>
                </div>
                <div class="mt-2 text-xs text-slate-500 md:mt-0">
                    <span class="font-medium text-slate-700" x-text="vendorRows.length"></span>
                    vendors in view
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-2">Vendor</th>
                            <th class="px-4 py-2">Total</th>
                            <th class="px-4 py-2">PO count</th>
                            <th class="px-4 py-2 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" x-show="vendorRows.length">
                        <template x-for="vendor in vendorRows" :key="vendor.vendor_name">
                            <tr
                                class="group cursor-pointer transition-colors hover:bg-slate-50"
                                @click="openVendorTotals(vendor.vendor_name)"
                            >
                                <td class="px-4 py-2 text-sm font-medium text-slate-900"
                                    x-text="vendor.vendor_name"></td>
                                <td class="px-4 py-2 text-sm text-slate-700"
                                    x-text="formatCurrency(vendor.total)"></td>
                                <td class="px-4 py-2 text-sm text-slate-700"
                                    x-text="vendor.po_count"></td>
                                <td class="px-4 py-2 text-right">
                                    <div class="inline-flex gap-2">
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-full border border-slate-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1"
                                            @click.stop="openVendorTotals(vendor.vendor_name)"
                                        >
                                            Monthly trend
                                        </button>
                                        <button
                                            type="button"
                                            class="inline-flex items-center rounded-full border border-sky-500 bg-sky-50 px-2.5 py-1 text-xs font-medium text-sky-700 shadow-sm hover:bg-sky-100 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1"
                                            @click.stop="openVendorPOs(vendor.vendor_name)"
                                        >
                                            View POs
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                    <tbody x-show="!vendorRows.length">
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">
                                No data available for the selected month.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top vendors modal --}}
        <div
            x-cloak
            x-show="showTopVendors"
            class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50"
        >
            <div
                class="w-full max-w-lg rounded-2xl bg-white shadow-xl"
                @click.stop
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">
                            Top 5 vendors
                        </h3>
                        <p class="text-xs text-slate-500" x-text="month ? `For period ${month}` : ''"></p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        @click="showTopVendors = false"
                    >
                        <span class="sr-only">Close</span>
                        ×
                    </button>
                </div>

                <div class="px-4 py-3">
                    <template x-if="!topVendors || !topVendors.length">
                        <p class="text-sm text-slate-500">
                            No data available for the selected month.
                        </p>
                    </template>

                    <ul class="divide-y divide-slate-100" x-show="topVendors && topVendors.length">
                        <template x-for="(vendor, idx) in topVendors" :key="vendor.vendor_name">
                            <li class="flex items-center justify-between py-2">
                                <div class="text-sm font-medium text-slate-900">
                                    <span class="mr-2 text-xs font-semibold text-slate-400"
                                          x-text="'#' + (idx + 1)"></span>
                                    <span x-text="vendor.vendor_name"></span>
                                </div>
                                <div class="text-sm font-semibold text-sky-600"
                                     x-text="formatCurrency(vendor.total)"></div>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Vendor monthly totals modal --}}
        <div
            x-cloak
            x-show="showVendorTotals"
            class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50"
        >
            <div
                class="w-full max-w-2xl rounded-2xl bg-white shadow-xl"
                @click.stop
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900"
                            x-text="`Monthly totals – ${vendorForTotals || ''}`"></h3>
                        <p class="text-xs text-slate-500">
                            Spend trend across months.
                        </p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        @click="showVendorTotals = false"
                    >
                        <span class="sr-only">Close</span>
                        ×
                    </button>
                </div>

                <div class="px-4 py-3">
                    <div class="relative h-64">
                        <canvas x-ref="vendorMonthlyChart" class="h-full w-full"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Vendor PO details modal --}}
        <div
            x-cloak
            x-show="showVendorPOs"
            class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/50"
        >
            <div
                class="w-full max-w-3xl rounded-2xl bg-white shadow-xl"
                @click.stop
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900"
                            x-text="`Purchase orders – ${vendorForDetails || ''}`"></h3>
                        <p class="text-xs text-slate-500" x-text="month ? `For period ${month}` : ''"></p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                        @click="showVendorPOs = false"
                    >
                        <span class="sr-only">Close</span>
                        ×
                    </button>
                </div>

                <div class="max-h-[70vh] overflow-y-auto px-4 py-3">
                    <table class="min-w-full divide-y divide-slate-100 text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                <th class="px-3 py-2">Invoice date</th>
                                <th class="px-3 py-2">PO number</th>
                                <th class="px-3 py-2 text-right">Total</th>
                                <th class="px-3 py-2">Status</th>
                                <th class="px-3 py-2 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100" x-show="vendorDetailRows.length">
                            <template x-for="po in vendorDetailRows" :key="po.id">
                                <tr>
                                    <td class="px-3 py-2 text-sm text-slate-700"
                                        x-text="po.invoice_date"></td>
                                    <td class="px-3 py-2 text-sm font-medium text-slate-900"
                                        x-text="po.po_number"></td>
                                    <td class="px-3 py-2 text-right text-sm text-slate-700"
                                        x-text="formatCurrency(po.total)"></td>
                                    <td class="px-3 py-2">
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                                            :class="statusBadgeClass(po.status)"
                                            x-text="po.status"
                                        ></span>
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <a
                                            :href="`/purchaseOrder/${po.id}`"
                                            class="inline-flex items-center rounded-full border border-slate-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50"
                                        >
                                            View
                                        </a>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                        <tbody x-show="!vendorDetailRows.length">
                            <tr>
                                <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500">
                                    No purchase orders found for this vendor in the selected month.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection

@pushOnce('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        function poDashboard(config) {
            return {
                month: config.initialMonth,
                endpoints: {
                    filter: config.filterUrl,
                    vendorMonthly: config.vendorMonthlyUrl,
                    vendorDetails: config.vendorDetailsUrl,
                },
                statusCounts: config.initialStatusCounts || {},
                categoryChartData: config.initialCategoryData || [],
                vendorRows: config.initialVendorTotals || [],
                topVendors: config.initialTopVendors || [],
                vendorDetailRows: [],
                vendorForTotals: null,
                vendorForDetails: null,
                loading: false,
                showTopVendors: false,
                showVendorTotals: false,
                showVendorPOs: false,

                monthlyChart: null,
                statusChart: null,
                categoryChart: null,
                vendorMonthlyChart: null,

                init() {
                    this.initStaticCharts();
                    this.fetchMonthData(this.month);
                },

                onMonthChange() {
                    this.fetchMonthData(this.month);
                },

                initStaticCharts() {
                    const ctxStatus = this.$refs.statusChart?.getContext('2d');
                    const ctxCategory = this.$refs.categoryChart?.getContext('2d');

                    if (ctxStatus) {
                        const s = this.statusCounts;
                        this.statusChart = new Chart(ctxStatus, {
                            type: 'pie',
                            data: {
                                labels: ['Approved', 'Waiting', 'Rejected', 'Canceled'],
                                datasets: [{
                                    data: [
                                        s.approved || 0,
                                        s.waiting || 0,
                                        s.rejected || 0,
                                        s.canceled || 0,
                                    ],
                                    borderWidth: 1,
                                }],
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { position: 'bottom' },
                                },
                            },
                        });
                    }

                    if (ctxCategory) {
                        const labels = (this.categoryChartData || []).map(i => i.label);
                        const data = (this.categoryChartData || []).map(i => i.count);

                        this.categoryChart = new Chart(ctxCategory, {
                            type: 'pie',
                            data: {
                                labels,
                                datasets: [{
                                    data,
                                    borderWidth: 1,
                                }],
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { position: 'bottom' },
                                },
                            },
                        });
                    }
                },

                fetchMonthData(month) {
                    if (!this.endpoints.filter) return;
                    this.loading = true;

                    fetch(`${this.endpoints.filter}?month=${encodeURIComponent(month)}`)
                        .then(res => res.json())
                        .then(data => {
                            this.month = month;
                            this.topVendors = data.topVendors || [];
                            this.vendorRows = data.vendorTotals || [];
                            this.refreshMonthlyChart(data.chartData || {});
                        })
                        .catch(err => console.error('Error loading dashboard data', err))
                        .finally(() => {
                            this.loading = false;
                        });
                },

                refreshMonthlyChart(chartData) {
                    const ctx = this.$refs.monthlyChart?.getContext('2d');
                    if (!ctx) return;

                    if (this.monthlyChart) {
                        this.monthlyChart.destroy();
                    }

                    const labels = chartData.labels || [];
                    const totals = (chartData.totals || []).map(Number);
                    const fmt = new Intl.NumberFormat('id-ID');

                    this.monthlyChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Total (IDR)',
                                data: totals,
                                borderWidth: 1,
                            }],
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: v => 'IDR ' + fmt.format(v),
                                    },
                                },
                            },
                        },
                    });
                },

                openTopVendors() {
                    this.showTopVendors = true;
                },

                openVendorTotals(vendorName) {
                    if (!this.endpoints.vendorMonthly) return;
                    this.vendorForTotals = vendorName;
                    this.showVendorTotals = true;

                    fetch(`${this.endpoints.vendorMonthly}?vendor=${encodeURIComponent(vendorName)}`)
                        .then(res => res.json())
                        .then(data => this.renderVendorMonthlyChart(data || []))
                        .catch(err => console.error('Error fetching vendor monthly totals', err));
                },

                renderVendorMonthlyChart(rows) {
                    const ctx = this.$refs.vendorMonthlyChart?.getContext('2d');
                    if (!ctx) return;

                    if (this.vendorMonthlyChart) {
                        this.vendorMonthlyChart.destroy();
                    }

                    const labels = rows.map(r => r.month);
                    const data = rows.map(r => Number(r.total || 0));
                    const fmt = new Intl.NumberFormat('id-ID');

                    this.vendorMonthlyChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                label: 'Total (IDR)',
                                data,
                                borderWidth: 1,
                            }],
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: v => 'IDR ' + fmt.format(v),
                                    },
                                },
                            },
                        },
                    });
                },

                openVendorPOs(vendorName) {
                    if (!this.endpoints.vendorDetails) return;
                    this.vendorForDetails = vendorName;
                    this.showVendorPOs = true;
                    this.vendorDetailRows = [];

                    fetch(
                        `${this.endpoints.vendorDetails}?vendor=${encodeURIComponent(vendorName)}&month=${encodeURIComponent(this.month)}`
                    )
                        .then(res => res.json())
                        .then(data => {
                            this.vendorDetailRows = data || [];
                        })
                        .catch(err => console.error('Error fetching vendor POs', err));
                },

                statusBadgeClass(status) {
                    switch ((status || '').toLowerCase()) {
                        case 'approved':
                            return 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200';
                        case 'rejected':
                            return 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-200';
                        case 'canceled':
                            return 'bg-slate-100 text-slate-700 ring-1 ring-inset ring-slate-200';
                        default:
                            return 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200';
                    }
                },

                formatCurrency(value) {
                    const fmt = new Intl.NumberFormat('id-ID');
                    return 'IDR ' + fmt.format(Number(value || 0));
                },
            };
        }
    </script>
@endpushOnce