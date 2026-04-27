# Purchase Order Management Dashboard - TALL Stack Architecture

## Executive Overview

This document outlines the design and implementation of a high-performance, professional-grade Purchase Order Management Dashboard using the TALL stack (Tailwind CSS, Alpine.js, Laravel, Livewire). The dashboard is optimized for procurement workflows with seamless Chart.js integration and real-time data visualization.

**Target Architecture:** Enterprise-grade procurement analytics platform
**Performance Target:** <2s load time, real-time filtering, <500ms chart updates
**Scalability:** Support 10,000+ POs with efficient pagination and caching

---

## 1. Backend Architecture (Laravel & Livewire)

### Component Hierarchy

```
App\Livewire\
├── PurchaseOrderDashboard.php          # Main dashboard controller
├── Components\
│   ├── MetricCard.php                  # KPI summary cards
│   ├── ChartContainer.php              # Chart wrapper component
│   ├── ActivityFeed.php                # Recent activity table
│   ├── QuickActions.php                # Action buttons panel
│   └── Filters\
│       ├── DateRangeFilter.php         # Date picker component
│       ├── StatusFilter.php            # Multi-select status filter
│       └── SupplierFilter.php          # Supplier dropdown
├── Modals\
│   ├── CreatePurchaseOrderModal.php    # New PO creation
│   ├── BulkActionsModal.php            # Bulk operations
│   └── SupplierDetailsModal.php        # Supplier analytics
└── Traits\
    ├── WithChartData.php               # Chart data processing
    ├── WithFiltering.php               # Filter logic
    └── WithCaching.php                 # Cache management
```

### Core Dashboard Component Architecture

```php
<?php

namespace App\Livewire;

use App\Services\PurchaseOrderAnalyticsService;
use App\Services\PurchaseOrderService;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseOrderDashboard extends Component
{
    use WithPagination, WithChartData, WithFiltering, WithCaching;

    // Filter Properties
    public $dateRange = 'last_30_days';
    public $selectedStatuses = [];
    public $selectedSuppliers = [];
    public $selectedCategories = [];

    // Data Properties
    public $metrics = [];
    public $chartData = [];
    public $recentActivity = [];
    public $topSuppliers = [];

    // UI State
    public $showCreateModal = false;
    public $showBulkModal = false;
    public $loading = false;

    protected $queryString = [
        'dateRange' => ['except' => 'last_30_days'],
        'selectedStatuses' => ['except' => []],
        'selectedSuppliers' => ['except' => []],
        'selectedCategories' => ['except' => []],
    ];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function updatedDateRange()
    {
        $this->loadDashboardData();
    }

    public function updatedSelectedStatuses()
    {
        $this->loadDashboardData();
    }

    public function updatedSelectedSuppliers()
    {
        $this->loadDashboardData();
    }

    public function loadDashboardData()
    {
        $this->loading = true;

        $cacheKey = $this->getCacheKey();
        $filters = $this->buildFilters();

        $this->metrics = Cache::remember($cacheKey . '_metrics', 300, function () use ($filters) {
            return app(PurchaseOrderAnalyticsService::class)->getMetrics($filters);
        });

        $this->chartData = Cache::remember($cacheKey . '_charts', 300, function () use ($filters) {
            return app(PurchaseOrderAnalyticsService::class)->getChartData($filters);
        });

        $this->recentActivity = app(PurchaseOrderService::class)->getRecentActivity($filters, 10);
        $this->topSuppliers = app(PurchaseOrderAnalyticsService::class)->getTopSuppliers($filters, 5);

        $this->loading = false;

        // Dispatch data to JavaScript for Chart.js
        $this->dispatch('dashboardDataUpdated', [
            'spendingTrends' => $this->chartData['spendingTrends'],
            'supplierDistribution' => $this->chartData['supplierDistribution'],
            'statusBreakdown' => $this->chartData['statusBreakdown'],
        ]);
    }

    public function exportReport()
    {
        return app(PurchaseOrderAnalyticsService::class)->exportReport($this->buildFilters());
    }

    public function generateBulkExport()
    {
        $this->showBulkModal = true;
    }

    private function buildFilters(): array
    {
        return [
            'date_range' => $this->parseDateRange($this->dateRange),
            'statuses' => $this->selectedStatuses,
            'suppliers' => $this->selectedSuppliers,
            'categories' => $this->selectedCategories,
        ];
    }

    private function getCacheKey(): string
    {
        return 'po_dashboard_' . md5(serialize($this->buildFilters()));
    }

    public function render()
    {
        return view('livewire.purchase-order.dashboard', [
            'filters' => $this->getFilterOptions(),
        ])->layout('layouts.dashboard');
    }
}
```

### Analytics Service Architecture

```php
<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseOrderAnalyticsService
{
    public function getMetrics(array $filters = []): array
    {
        $query = $this->buildBaseQuery($filters);

        return [
            'totalSpend' => $query->sum('total'),
            'openOrders' => (clone $query)->whereIn('status', [1, 2])->count(), // Draft, Waiting
            'pendingApprovals' => (clone $query)->where('status', 2)->count(), // Waiting
            'overdueDeliveries' => $this->getOverdueCount($filters),
            'averageOrderValue' => $query->avg('total'),
            'supplierCount' => (clone $query)->distinct('vendor_name')->count('vendor_name'),
        ];
    }

    public function getChartData(array $filters = []): array
    {
        return [
            'spendingTrends' => $this->getSpendingTrends($filters),
            'supplierDistribution' => $this->getSupplierDistribution($filters),
            'statusBreakdown' => $this->getStatusBreakdown($filters),
            'categoryTrends' => $this->getCategoryTrends($filters),
        ];
    }

    private function getSpendingTrends(array $filters): array
    {
        $dateRange = $filters['date_range'] ?? [now()->subDays(30), now()];

        return PurchaseOrder::selectRaw("
                DATE_FORMAT(invoice_date, '%Y-%m-%d') as date,
                SUM(total) as total_spend,
                COUNT(*) as order_count
            ")
            ->whereBetween('invoice_date', $dateRange)
            ->when($filters['statuses'] ?? null, fn($q) => $q->whereIn('status', $filters['statuses']))
            ->when($filters['suppliers'] ?? null, fn($q) => $q->whereIn('vendor_name', $filters['suppliers']))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'total_spend' => (float) $item->total_spend,
                    'order_count' => (int) $item->order_count,
                ];
            })
            ->toArray();
    }

    private function getSupplierDistribution(array $filters): array
    {
        $dateRange = $filters['date_range'] ?? [now()->subDays(30), now()];

        return PurchaseOrder::selectRaw("
                vendor_name,
                SUM(total) as total_spend,
                COUNT(*) as order_count
            ")
            ->whereBetween('invoice_date', $dateRange)
            ->when($filters['statuses'] ?? null, fn($q) => $q->whereIn('status', $filters['statuses']))
            ->groupBy('vendor_name')
            ->orderByDesc('total_spend')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'supplier' => $item->vendor_name,
                    'total_spend' => (float) $item->total_spend,
                    'order_count' => (int) $item->order_count,
                ];
            })
            ->toArray();
    }

    private function getStatusBreakdown(array $filters): array
    {
        $dateRange = $filters['date_range'] ?? [now()->subDays(30), now()];

        $statusLabels = [
            1 => 'Draft',
            2 => 'Waiting Approval',
            3 => 'Approved',
            4 => 'Rejected',
            5 => 'Cancelled',
        ];

        $results = PurchaseOrder::selectRaw("
                status,
                COUNT(*) as count,
                SUM(total) as total_value
            ")
            ->whereBetween('invoice_date', $dateRange)
            ->when($filters['suppliers'] ?? null, fn($q) => $q->whereIn('vendor_name', $filters['suppliers']))
            ->when($filters['categories'] ?? null, fn($q) => $q->whereIn('purchase_order_category_id', $filters['categories']))
            ->groupBy('status')
            ->get();

        return collect($statusLabels)->map(function ($label, $status) use ($results) {
            $result = $results->firstWhere('status', $status);
            return [
                'status' => $label,
                'count' => $result ? (int) $result->count : 0,
                'total_value' => $result ? (float) $result->total_value : 0,
            ];
        })->values()->toArray();
    }

    private function getOverdueCount(array $filters): int
    {
        return PurchaseOrder::where('status', 3) // Approved
            ->where('tanggal_pembayaran', '<', now())
            ->whereBetween('invoice_date', $filters['date_range'] ?? [now()->subDays(30), now()])
            ->count();
    }

    private function buildBaseQuery(array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query = PurchaseOrder::query();

        if (isset($filters['date_range'])) {
            $query->whereBetween('invoice_date', $filters['date_range']);
        }

        if (!empty($filters['statuses'])) {
            $query->whereIn('status', $filters['statuses']);
        }

        if (!empty($filters['suppliers'])) {
            $query->whereIn('vendor_name', $filters['suppliers']);
        }

        if (!empty($filters['categories'])) {
            $query->whereIn('purchase_order_category_id', $filters['categories']);
        }

        return $query;
    }
}
```

---

## 2. Frontend Architecture (Alpine.js & Tailwind CSS)

### Design System Overview

#### Color Palette

```css
/* Primary Colors */
--color-primary-50: #f0f9ff;
--color-primary-100: #e0f2fe;
--color-primary-500: #3b82f6;
--color-primary-600: #2563eb;
--color-primary-700: #1d4ed8;

/* Neutral Colors */
--color-slate-50: #f8fafc;
--color-slate-100: #f1f5f9;
--color-slate-200: #e2e8f0;
--color-slate-500: #64748b;
--color-slate-600: #475569;
--color-slate-900: #0f172a;

/* Status Colors */
--color-success: #10b981;
--color-warning: #f59e0b;
--color-error: #ef4444;
--color-info: #3b82f6;
```

#### Typography Scale

```css
/* Headings */
.text-display {
  font-size: 2.5rem;
  line-height: 1.1;
  font-weight: 700;
}
.text-heading-1 {
  font-size: 2rem;
  line-height: 1.2;
  font-weight: 600;
}
.text-heading-2 {
  font-size: 1.5rem;
  line-height: 1.3;
  font-weight: 600;
}
.text-heading-3 {
  font-size: 1.25rem;
  line-height: 1.4;
  font-weight: 600;
}

/* Body Text */
.text-body-large {
  font-size: 1.125rem;
  line-height: 1.6;
}
.text-body {
  font-size: 1rem;
  line-height: 1.6;
}
.text-body-small {
  font-size: 0.875rem;
  line-height: 1.5;
}
```

#### Spacing Scale

```css
/* Spacing tokens */
.space-1: 0.25rem (4px)
.space-2: 0.5rem (8px)
.space-3: 0.75rem (12px)
.space-4: 1rem (16px)
.space-5: 1.25rem (20px)
.space-6: 1.5rem (24px)
.space-8: 2rem (32px)
.space-10: 2.5rem (40px)
.space-12: 3rem (48px)
.space-16: 4rem (64px)
```

### Component Library

#### Metric Card Component

```blade
<!-- resources/views/components/metric-card.blade.php -->
@props([
    'title' => '',
    'value' => '',
    'change' => null,
    'changeType' => 'positive',
    'icon' => '',
    'loading' => false
])

<div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium text-slate-600">{{ $title }}</p>
            @if($loading)
                <div class="mt-2 h-8 bg-slate-200 rounded animate-pulse"></div>
            @else
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ $value }}</p>
            @endif
        </div>
        @if($icon)
            <div class="h-12 w-12 rounded-lg bg-slate-100 flex items-center justify-center">
                <svg class="h-6 w-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $icon !!}
                </svg>
            </div>
        @endif
    </div>

    @if($change !== null)
        <div class="mt-4 flex items-center">
            @if($changeType === 'positive')
                <svg class="h-4 w-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <span class="ml-1 text-sm font-medium text-green-600">+{{ $change }}%</span>
            @else
                <svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                </svg>
                <span class="ml-1 text-sm font-medium text-red-600">{{ $change }}%</span>
            @endif
            <span class="ml-1 text-sm text-slate-500">from last month</span>
        </div>
    @endif
</div>
```

#### Status Badge Component

```blade
<!-- resources/views/components/status-badge.blade.php -->
@props([
    'status' => '',
    'size' => 'sm',
    'variant' => 'filled'
])

@php
    $statusConfig = [
        'draft' => ['label' => 'Draft', 'color' => 'slate'],
        'waiting' => ['label' => 'Waiting Approval', 'color' => 'yellow'],
        'approved' => ['label' => 'Approved', 'color' => 'green'],
        'rejected' => ['label' => 'Rejected', 'color' => 'red'],
        'cancelled' => ['label' => 'Cancelled', 'color' => 'gray'],
        'shipped' => ['label' => 'Shipped', 'color' => 'blue'],
        'delivered' => ['label' => 'Delivered', 'color' => 'emerald'],
    ];

    $config = $statusConfig[strtolower($status)] ?? ['label' => ucfirst($status), 'color' => 'slate'];
    $color = $config['color'];

    $sizeClasses = [
        'xs' => 'px-2 py-0.5 text-xs',
        'sm' => 'px-2.5 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-sm',
        'lg' => 'px-4 py-1.5 text-base',
    ];

    $variantClasses = [
        'filled' => "bg-{$color}-100 text-{$color}-800 border border-{$color}-200",
        'outlined' => "border border-{$color}-300 text-{$color}-700 bg-white",
        'subtle' => "bg-{$color}-50 text-{$color}-600",
    ];
@endphp

<span class="inline-flex items-center rounded-full font-medium {{ $sizeClasses[$size] ?? $sizeClasses['sm'] }} {{ $variantClasses[$variant] ?? $variantClasses['filled'] }}">
    <span class="relative inline-flex items-center">
        @if(in_array(strtolower($status), ['waiting', 'processing']))
            <svg class="w-3 h-3 mr-1.5 -ml-0.5 animate-spin" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
            </svg>
        @endif
        {{ $config['label'] }}
    </span>
</span>
```

### Main Dashboard Layout

```blade
<!-- resources/views/livewire/purchase-order/dashboard.blade.php -->
<x-app-layout>
    <div class="min-h-screen bg-slate-50">
        <!-- Sidebar -->
        <div class="flex">
            <!-- Sidebar Navigation -->
            <div class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 bg-slate-900">
                <div class="flex flex-col flex-grow pt-5 pb-4 overflow-y-auto">
                    <div class="flex items-center flex-shrink-0 px-4">
                        <h1 class="text-xl font-bold text-white">Procurement Hub</h1>
                    </div>
                    <nav class="mt-8 flex-1 px-2 space-y-1">
                        <a href="#" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-slate-300 hover:bg-slate-700 hover:text-white {{ request()->routeIs('po.dashboard') ? 'bg-slate-800 text-white' : '' }}">
                            <svg class="mr-3 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Dashboard
                        </a>
                        <!-- Other navigation items -->
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:pl-64 flex flex-col flex-1">
                <!-- Top Navigation -->
                <div class="sticky top-0 z-10 flex-shrink-0 flex h-16 bg-white shadow border-b border-slate-200">
                    <div class="flex-1 px-4 flex justify-between">
                        <div class="flex-1 flex">
                            <div class="w-full flex md:ml-0">
                                <label for="search-field" class="sr-only">Search</label>
                                <div class="relative w-full text-slate-400 focus-within:text-slate-600">
                                    <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.817A6 6 0 012 8z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <input wire:model.live.debounce.300ms="globalSearch" id="search-field" class="block w-full h-full pl-8 pr-3 py-2 border-transparent text-slate-900 placeholder-slate-500 focus:outline-none focus:placeholder-slate-400 focus:ring-0 focus:border-transparent sm:text-sm" placeholder="Search purchase orders..." type="search">
                                </div>
                            </div>
                        </div>
                        <div class="ml-4 flex items-center md:ml-6">
                            <!-- Profile dropdown -->
                        </div>
                    </div>
                </div>

                <!-- Page Content -->
                <main class="flex-1 relative overflow-y-auto focus:outline-none">
                    <div class="py-6">
                        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                            <!-- Page Header -->
                            <div class="md:flex md:items-center md:justify-between">
                                <div class="flex-1 min-w-0">
                                    <h2 class="text-2xl font-bold leading-7 text-slate-900 sm:text-3xl sm:truncate">
                                        Purchase Order Dashboard
                                    </h2>
                                    <p class="mt-1 text-sm text-slate-500">
                                        Monitor procurement performance and track purchase order metrics
                                    </p>
                                </div>
                                <div class="mt-4 flex-shrink-0 flex md:mt-0 md:ml-4">
                                    <button wire:click="generateBulkExport" type="button" class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-md shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Export
                                    </button>
                                    <button wire:click="$set('showCreateModal', true)" type="button" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        New Purchase Order
                                    </button>
                                </div>
                            </div>

                            <!-- Filters -->
                            <div class="mt-6 bg-white shadow rounded-lg">
                                <div class="px-4 py-5 sm:p-6">
                                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                        <div>
                                            <label for="dateRange" class="block text-sm font-medium text-slate-700">Date Range</label>
                                            <select wire:model.live="dateRange" id="dateRange" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-slate-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                <option value="last_7_days">Last 7 days</option>
                                                <option value="last_30_days">Last 30 days</option>
                                                <option value="last_90_days">Last 90 days</option>
                                                <option value="last_year">Last year</option>
                                                <option value="custom">Custom range</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-slate-700">Status</label>
                                            <div class="mt-1 space-y-2">
                                                @foreach(['draft', 'waiting', 'approved', 'rejected'] as $status)
                                                    <label class="inline-flex items-center">
                                                        <input wire:model.live="selectedStatuses" type="checkbox" value="{{ $status }}" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                                        <span class="ml-2 text-sm text-slate-700 capitalize">{{ $status }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div>
                                            <label for="suppliers" class="block text-sm font-medium text-slate-700">Suppliers</label>
                                            <select wire:model.live="selectedSuppliers" id="suppliers" multiple class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-slate-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                @foreach($filters['suppliers'] ?? [] as $supplier)
                                                    <option value="{{ $supplier }}">{{ $supplier }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div>
                                            <label for="categories" class="block text-sm font-medium text-slate-700">Categories</label>
                                            <select wire:model.live="selectedCategories" id="categories" multiple class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-slate-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                                @foreach($filters['categories'] ?? [] as $category)
                                                    <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Metrics Cards -->
                            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                                <x-metric-card
                                    title="Total Spend"
                                    :value="number_format($metrics['totalSpend'] ?? 0, 0, ',', '.')"
                                    :change="12.5"
                                    icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1'></path>"
                                    :loading="$loading"
                                />

                                <x-metric-card
                                    title="Open Orders"
                                    :value="$metrics['openOrders'] ?? 0"
                                    :change="-5.2"
                                    change-type="negative"
                                    icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'></path>"
                                    :loading="$loading"
                                />

                                <x-metric-card
                                    title="Pending Approvals"
                                    :value="$metrics['pendingApprovals'] ?? 0"
                                    :change="8.1"
                                    icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'></path>"
                                    :loading="$loading"
                                />

                                <x-metric-card
                                    title="Overdue Deliveries"
                                    :value="$metrics['overdueDeliveries'] ?? 0"
                                    :change="-15.3"
                                    change-type="negative"
                                    icon="<path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z'></path>"
                                    :loading="$loading"
                                />
                            </div>

                            <!-- Charts Grid -->
                            <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- Spending Trends Chart -->
                                <div class="lg:col-span-2 bg-white shadow rounded-lg">
                                    <div class="px-4 py-5 sm:p-6">
                                        <h3 class="text-lg leading-6 font-medium text-slate-900">Spending Trends</h3>
                                        <p class="mt-1 max-w-2xl text-sm text-slate-500">Monthly expenditure on purchase orders</p>
                                    </div>
                                    <div class="px-4 pb-6">
                                        <canvas id="spendingTrendsChart" class="h-80 w-full"></canvas>
                                    </div>
                                </div>

                                <!-- Status Breakdown & Supplier Distribution -->
                                <div class="space-y-6">
                                    <!-- Status Breakdown -->
                                    <div class="bg-white shadow rounded-lg">
                                        <div class="px-4 py-5 sm:p-6">
                                            <h3 class="text-lg leading-6 font-medium text-slate-900">Order Status</h3>
                                            <p class="mt-1 max-w-2xl text-sm text-slate-500">Current status distribution</p>
                                        </div>
                                        <div class="px-4 pb-6">
                                            <canvas id="statusBreakdownChart" class="h-64 w-full"></canvas>
                                        </div>
                                    </div>

                                    <!-- Supplier Distribution -->
                                    <div class="bg-white shadow rounded-lg">
                                        <div class="px-4 py-5 sm:p-6">
                                            <h3 class="text-lg leading-6 font-medium text-slate-900">Top Suppliers</h3>
                                            <p class="mt-1 max-w-2xl text-sm text-slate-500">Spend distribution by supplier</p>
                                        </div>
                                        <div class="px-4 pb-6">
                                            <canvas id="supplierDistributionChart" class="h-64 w-full"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Activity -->
                            <div class="mt-6">
                                <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                    <div class="px-4 py-5 sm:px-6">
                                        <h3 class="text-lg leading-6 font-medium text-slate-900">Recent Activity</h3>
                                        <p class="mt-1 max-w-2xl text-sm text-slate-500">Latest purchase order updates and approvals</p>
                                    </div>
                                    <ul role="list" class="divide-y divide-slate-200">
                                        @forelse($recentActivity as $activity)
                                            <li>
                                                <div class="px-4 py-4 sm:px-6">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center">
                                                            <div class="flex-shrink-0">
                                                                <x-status-badge :status="$activity['status']" size="sm" />
                                                            </div>
                                                            <div class="ml-4">
                                                                <div class="text-sm font-medium text-slate-900">
                                                                    PO #{{ $activity['po_number'] }}
                                                                </div>
                                                                <div class="text-sm text-slate-500">
                                                                    {{ $activity['vendor_name'] }} • IDR {{ number_format($activity['total'], 0, ',', '.') }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="text-sm text-slate-500">
                                                            {{ $activity['created_at']->diffForHumans() }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="px-4 py-8 text-center text-slate-500">
                                                No recent activity found
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Create PO Modal -->
    @if($showCreateModal)
        <livewire:purchase-order.create-purchase-order-modal />
    @endif

    <!-- Bulk Actions Modal -->
    @if($showBulkModal)
        <livewire:purchase-order.bulk-actions-modal />
    @endif

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Chart data from Livewire
        let spendingTrendsData = @js($chartData['spendingTrends'] ?? []);
        let statusBreakdownData = @js($chartData['statusBreakdown'] ?? []);
        let supplierDistributionData = @js($chartData['supplierDistribution'] ?? []);

        document.addEventListener('livewire:init', () => {
            let spendingChart = null;
            let statusChart = null;
            let supplierChart = null;

            function createSpendingTrendsChart() {
                const ctx = document.getElementById('spendingTrendsChart');
                if (!ctx) return;

                if (spendingChart) spendingChart.destroy();

                spendingChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: spendingTrendsData.map(item => {
                            const date = new Date(item.date);
                            return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
                        }),
                        datasets: [{
                            label: 'Total Spend',
                            data: spendingTrendsData.map(item => item.total_spend),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }, {
                            label: 'Order Count',
                            data: spendingTrendsData.map(item => item.order_count),
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            tension: 0.4,
                            yAxisID: 'y1'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: { display: true },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        if (context.datasetIndex === 0) {
                                            return 'Spend: IDR ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                                        }
                                        return 'Orders: ' + context.parsed.y;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: { display: true, text: 'Total Spend (IDR)' },
                                ticks: {
                                    callback: function(value) {
                                        return 'IDR ' + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                                    }
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: { display: true, text: 'Order Count' },
                                grid: { drawOnChartArea: false }
                            }
                        }
                    }
                });
            }

            function createStatusBreakdownChart() {
                const ctx = document.getElementById('statusBreakdownChart');
                if (!ctx) return;

                if (statusChart) statusChart.destroy();

                statusChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: statusBreakdownData.map(item => item.status),
                        datasets: [{
                            label: 'Order Count',
                            data: statusBreakdownData.map(item => item.count),
                            backgroundColor: [
                                'rgba(100, 116, 139, 0.8)', // Draft - slate
                                'rgba(245, 158, 11, 0.8)',  // Waiting - amber
                                'rgba(16, 185, 129, 0.8)',  // Approved - emerald
                                'rgba(239, 68, 68, 0.8)',   // Rejected - red
                            ],
                            borderColor: [
                                'rgb(100, 116, 139)',
                                'rgb(245, 158, 11)',
                                'rgb(16, 185, 129)',
                                'rgb(239, 68, 68)',
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const item = statusBreakdownData[context.dataIndex];
                                        return [
                                            `Count: ${item.count}`,
                                            `Value: IDR ${new Intl.NumberFormat('id-ID').format(item.total_value)}`
                                        ];
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: { display: true, text: 'Number of Orders' }
                            }
                        }
                    }
                });
            }

            function createSupplierDistributionChart() {
                const ctx = document.getElementById('supplierDistributionChart');
                if (!ctx) return;

                if (supplierChart) supplierChart.destroy();

                supplierChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: supplierDistributionData.map(item => item.supplier),
                        datasets: [{
                            data: supplierDistributionData.map(item => item.total_spend),
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.8)',   // blue
                                'rgba(168, 85, 247, 0.8)',   // violet
                                'rgba(236, 72, 153, 0.8)',   // pink
                                'rgba(16, 185, 129, 0.8)',   // emerald
                                'rgba(245, 158, 11, 0.8)',   // amber
                                'rgba(239, 68, 68, 0.8)',    // red
                                'rgba(100, 116, 139, 0.8)',  // slate
                                'rgba(6, 182, 212, 0.8)',    // cyan
                            ],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 12, font: { size: 11 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const item = supplierDistributionData[context.dataIndex];
                                        const percentage = ((item.total_spend / supplierDistributionData.reduce((sum, item) => sum + item.total_spend, 0)) * 100).toFixed(1);
                                        return [
                                            `Spend: IDR ${new Intl.NumberFormat('id-ID').format(item.total_spend)}`,
                                            `Orders: ${item.order_count}`,
                                            `Share: ${percentage}%`
                                        ];
                                    }
                                }
                            }
                        },
                        cutout: '60%'
                    }
                });
            }

            function updateAllCharts() {
                createSpendingTrendsChart();
                createStatusBreakdownChart();
                createSupplierDistributionChart();
            }

            // Initial chart render
            updateAllCharts();

            // Listen for Livewire data updates
            Livewire.on('dashboardDataUpdated', (data) => {
                console.log('Dashboard data updated:', data);

                if (data.spendingTrends) {
                    spendingTrendsData = Array.isArray(data.spendingTrends) ? data.spendingTrends : [];
                }
                if (data.statusBreakdown) {
                    statusBreakdownData = Array.isArray(data.statusBreakdown) ? data.statusBreakdown : [];
                }
                if (data.supplierDistribution) {
                    supplierDistributionData = Array.isArray(data.supplierDistribution) ? data.supplierDistribution : [];
                }

                updateAllCharts();
            });
        });
    </script>
    @endpush
</x-app-layout>
```

---

## 3. Laravel-to-Chart.js Data Bridge Implementation

### Livewire Event Dispatch Pattern

The bridge between Laravel data and Chart.js follows this pattern:

1. **Laravel Data Processing**: Complex Eloquent queries with aggregations
2. **Livewire Event Dispatch**: Clean data arrays sent to JavaScript
3. **JavaScript Data Reception**: Event listeners update chart data
4. **Chart.js Rendering**: Dynamic chart updates with new data

### Performance Optimizations

#### Database Query Optimization

```php
// Use raw queries for aggregations to avoid N+1 problems
$spendingTrends = PurchaseOrder::selectRaw("
        DATE_FORMAT(invoice_date, '%Y-%m-%d') as date,
        SUM(total) as total_spend,
        COUNT(*) as order_count
    ")
    ->whereBetween('invoice_date', $dateRange)
    ->groupBy('date')
    ->orderBy('date')
    ->get();
```

#### Caching Strategy

```php
// Cache expensive analytics for 5 minutes
$metrics = Cache::remember($cacheKey . '_metrics', 300, function () use ($filters) {
    return app(PurchaseOrderAnalyticsService::class)->getMetrics($filters);
});
```

#### Real-time Updates

```javascript
// Event-driven chart updates without page refreshes
Livewire.on('dashboardDataUpdated', (data) => {
  // Update chart data and re-render
  spendingTrendsData = data.spendingTrends;
  updateAllCharts();
});
```

### Chart Configuration Best Practices

#### Responsive Charts

```javascript
options: {
    responsive: true,
    maintainAspectRatio: false, // Allow flexible sizing
    plugins: {
        legend: { position: 'bottom' }
    }
}
```

#### Performance Optimization

```javascript
// Destroy existing charts before creating new ones
if (chartInstance) {
  chartInstance.destroy();
}
```

#### Data Formatting

```javascript
// Proper number formatting for currency
ticks: {
    callback: function(value) {
        return 'IDR ' + new Intl.NumberFormat('id-ID').format(value);
    }
}
```

---

## 4. Tailwind CSS Design System

### Component Architecture

#### Base Components

- **Buttons**: Primary, secondary, danger, ghost variants
- **Forms**: Input, select, checkbox, radio components
- **Cards**: Metric cards, data cards, action cards
- **Tables**: Data tables with sorting and pagination
- **Modals**: Overlay modals for actions and details

#### Layout System

- **Sidebar Navigation**: Collapsible sidebar with menu items
- **Top Header**: Search, notifications, user menu
- **Content Grid**: Responsive grid system for dashboard widgets
- **Breadcrumb Navigation**: Page hierarchy indication

#### Color System

- **Primary**: Indigo (#3b82f6) for actions and highlights
- **Neutral**: Slate (#64748b) for text and backgrounds
- **Status Colors**: Green (success), Yellow (warning), Red (error)
- **Semantic Colors**: Each status has consistent color coding

#### Typography Scale

- **Display**: 2.5rem (40px) for main headings
- **H1**: 2rem (32px) for page titles
- **H2**: 1.5rem (24px) for section headers
- **Body**: 1rem (16px) for content
- **Small**: 0.875rem (14px) for metadata

### Responsive Design Principles

#### Mobile-First Approach

```css
/* Mobile first, then tablet, then desktop */
.grid-cols-1 {
  /* Mobile: single column */
}
md:grid-cols-2 {
  /* Tablet: two columns */
}
lg:grid-cols-4 {
  /* Desktop: four columns */
}
```

#### Breakpoint System

- **sm**: 640px (mobile landscape)
- **md**: 768px (tablet)
- **lg**: 1024px (desktop)
- **xl**: 1280px (large desktop)

#### Flexible Layouts

```css
/* Use flexbox for responsive alignment */
.flex {
  /* Base flex container */
}
flex-col md:flex-row {
  /* Column on mobile, row on desktop */
}
```

### Accessibility Standards

#### Color Contrast

- **Text on background**: Minimum 4.5:1 contrast ratio
- **Interactive elements**: Clear focus indicators
- **Status colors**: Distinct colors for different states

#### Keyboard Navigation

- **Tab order**: Logical navigation through interactive elements
- **Focus management**: Visible focus indicators
- **Shortcut keys**: Keyboard shortcuts for common actions

#### Screen Reader Support

- **Semantic HTML**: Proper heading hierarchy and landmarks
- **ARIA labels**: Descriptive labels for complex interactions
- **Alt text**: Alternative text for all images and icons

---

## Implementation Roadmap

### Phase 1: Core Architecture (Week 1-2)

- [ ] Set up Livewire component architecture
- [ ] Implement PurchaseOrderAnalyticsService
- [ ] Create basic metric cards and layout
- [ ] Set up Chart.js integration foundation

### Phase 2: Data Visualization (Week 3-4)

- [ ] Implement spending trends line chart
- [ ] Create supplier distribution doughnut chart
- [ ] Build status breakdown bar chart
- [ ] Add real-time chart updates

### Phase 3: Advanced Features (Week 5-6)

- [ ] Implement filtering system
- [ ] Add recent activity table
- [ ] Create bulk actions modal
- [ ] Set up export functionality

### Phase 4: Optimization & Polish (Week 7-8)

- [ ] Performance optimization
- [ ] Caching implementation
- [ ] Mobile responsiveness testing
- [ ] Accessibility audit

This comprehensive dashboard design provides a professional, scalable solution for procurement analytics with modern UX patterns and high-performance data visualization.
