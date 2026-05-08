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
                Operational command center for procurement intelligence and decision-making.
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

        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            {{-- Advanced Filters --}}
            <div class="flex flex-wrap items-center gap-3">
                {{-- Date Range Filter --}}
                <div class="flex items-center gap-2">
                    <label for="dateRange" class="text-xs font-medium text-slate-600">
                        Time Range
                    </label>
                    <select wire:model.live="selectedDateRange"
                            class="block rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="last_7_days">Last 7 days</option>
                        <option value="last_30_days">Last 30 days</option>
                        <option value="last_90_days">Last 90 days</option>
                        <option value="last_6_months">Last 6 months</option>
                        <option value="last_year">Last year</option>
                        <option value="custom">Custom range</option>
                    </select>
                </div>

                {{-- Status Filters --}}
                <div class="flex items-center gap-2">
                    <label class="text-xs font-medium text-slate-600">Status</label>
                    <div class="flex gap-1">
                        @foreach(['approved', 'waiting', 'rejected'] as $status)
                            <label class="inline-flex items-center">
                                <input wire:model.live="selectedStatuses"
                                       type="checkbox"
                                       value="{{ $status }}"
                                       class="rounded border-slate-300 text-sky-600 shadow-sm focus:border-sky-500 focus:ring-sky-500">
                                <span class="ml-1 text-xs text-slate-700 capitalize">{{ $status }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Category Filter --}}
                <div class="flex items-center gap-2">
                    <label for="categoryFilter" class="text-xs font-medium text-slate-600">
                        Category
                    </label>
                    <select wire:model.live="selectedCategory"
                            class="block rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-800 shadow-sm focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-500">
                        <option value="">All Categories</option>
                        @foreach($availableCategories ?? [] as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap gap-2">
                <button wire:click="refreshData"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>

                <button wire:click="exportDashboard"
                        class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export
                </button>

                <a href="{{ route('po.index') }}"
                   class="inline-flex items-center rounded-lg bg-sky-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1">
                    <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Manage POs
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



    {{-- Enhanced Supplier Intelligence & Quick Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        {{-- Supplier Intelligence Dashboard --}}
        <div class="lg:col-span-2">
            <x-supplier-lead-times :supplier-data="$operationalMetrics['supplierLeadTimes'] ?? []" />
        </div>

        {{-- Quick Actions & Pending Tasks --}}
        <div class="space-y-6">
            {{-- Pending Approvals --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Pending Actions</h3>
                <div class="space-y-3">
                    @if(($operationalMetrics['pendingActions']['pending_approvals'] ?? 0) > 0)
                        <div class="flex items-center justify-between p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-yellow-100 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-yellow-800">Approvals Pending</p>
                                    <p class="text-xs text-yellow-600">{{ $operationalMetrics['pendingActions']['pending_approvals'] }} items need review</p>
                                </div>
                            </div>
                            <a href="{{ route('po.approvals') }}" class="text-yellow-700 hover:text-yellow-800 text-sm font-medium">
                                Review →
                            </a>
                        </div>
                    @endif

                    @if(($operationalMetrics['pendingActions']['draft_pos'] ?? 0) > 0)
                        <div class="flex items-center justify-between p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-blue-800">Draft POs</p>
                                    <p class="text-xs text-blue-600">{{ $operationalMetrics['pendingActions']['draft_pos'] }} drafts need completion</p>
                                </div>
                            </div>
                            <a href="{{ route('po.index', ['status' => 'draft']) }}" class="text-blue-700 hover:text-blue-800 text-sm font-medium">
                                Complete →
                            </a>
                        </div>
                    @endif

                    @if(empty($operationalMetrics['pendingActions']) || ($operationalMetrics['pendingActions']['total_actions'] ?? 0) === 0)
                        <div class="text-center py-8 text-slate-500">
                            <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="mt-2 text-sm">All caught up!</p>
                            <p class="text-xs text-slate-400">No pending actions at this time</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Quick Create Actions --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    <button wire:click="showCreateModal"
                            class="w-full flex items-center justify-between p-3 bg-sky-50 border border-sky-200 rounded-lg hover:bg-sky-100 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-sky-100 flex items-center justify-center">
                                <svg class="h-4 w-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-sky-800">Create PO</p>
                                <p class="text-xs text-sky-600">Start new purchase order</p>
                            </div>
                        </div>
                        <svg class="h-4 w-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>

                    <button wire:click="showBulkActions"
                            class="w-full flex items-center justify-between p-3 bg-slate-50 border border-slate-200 rounded-lg hover:bg-slate-100 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
                                <svg class="h-4 w-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-800">Bulk Operations</p>
                                <p class="text-xs text-slate-600">Process multiple POs</p>
                            </div>
                        </div>
                        <svg class="h-4 w-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Modals --}}
    <livewire:purchase-order.top-vendors-modal :show-modal="$showTopVendorsModal" />
    <livewire:purchase-order.vendor-details-modal :show-modal="$showVendorModal" />
    <livewire:purchase-order.purchase-order-detail />
</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            console.log('🚀 Operational Command Center initialized');

            // Listen for dashboard data updates
            Livewire.on('dashboardDataUpdated', (data) => {
                console.log('📊 Dashboard data updated:', data);

                // Update operational metrics for real-time dashboard
                if (data.operationalMetrics) {
                    console.log('🎯 Operational metrics updated:', data.operationalMetrics);
                    updateOperationalMetrics(data.operationalMetrics);
                }
            });

            function updateOperationalMetrics(metrics) {
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

            console.log('✅ Operational Command Center ready');
        });
    </script>
@endpush
