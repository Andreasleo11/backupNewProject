<div x-data="{ 
    showFilters: false,
    selectedIds: @entangle('selectedIds').live
}" class="space-y-4">
    
    {{-- Background Polling Status --}}
    @if(!empty($processingIds))
        <div wire:poll.3s="checkProcessingStatus" class="hidden"></div>
    @endif

    {{-- Conditional rendering based on form mode --}}
    @if($formMode === 'index')
        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight uppercase">Purchase Orders</h1>
                <nav class="flex items-center gap-2 text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">
                    <a href="{{ route('po.dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                    <i class="bi bi-chevron-right text-[10px]"></i>
                    <span class="text-slate-600">List</span>
                </nav>
            </div>

            <div class="flex items-center gap-2">
                <div wire:loading.delay wire:target="search, statusFilter, vendorFilter, dateFrom, dateTo, amountFrom, amountTo, sortBy, sortDirection" class="flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-600 rounded-xl animate-pulse">
                    <div class="h-2 w-2 rounded-full bg-indigo-600 animate-bounce"></div>
                    <span class="text-xs font-black uppercase tracking-widest">Updating...</span>
                </div>
            </div>
        </div>

    {{-- Compact Stats Bar --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Pending Me --}}
        <button wire:click="filterByStat('pending_me')" 
                class="group bg-white rounded-2xl p-5 flex items-center gap-4 border border-slate-100 shadow-sm hover:shadow-md transition-all text-left relative overflow-hidden">
            <div class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl shadow-inner group-hover:bg-indigo-600 group-hover:text-white transition-all">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-wider mb-0.5">My Action</p>
                <p class="text-xl font-black text-slate-900 leading-none">{{ $this->stats['pending_me'] }}</p>
            </div>
        </button>

        {{-- Active Reviews --}}
        <button wire:click="filterByStat('in_review')"
                class="group bg-white rounded-2xl p-5 flex items-center gap-4 border border-slate-100 shadow-sm hover:shadow-md transition-all text-left relative overflow-hidden">
            <div class="h-12 w-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl shadow-inner group-hover:bg-amber-600 group-hover:text-white transition-all">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-wider mb-0.5">In Review</p>
                <p class="text-xl font-black text-slate-900 leading-none">{{ $this->stats['in_review'] }}</p>
            </div>
        </button>

        {{-- Monthly Rejections --}}
        <button wire:click="filterByStat('rejected')"
                class="group bg-white rounded-2xl p-5 flex items-center gap-4 border border-slate-100 shadow-sm hover:shadow-md transition-all text-left relative overflow-hidden">
            <div class="h-12 w-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl shadow-inner group-hover:bg-rose-600 group-hover:text-white transition-all">
                <i class="bi bi-x-octagon-fill"></i>
            </div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase tracking-wider mb-0.5">Rejected</p>
                <p class="text-xl font-black text-slate-900 leading-none">{{ $this->stats['rejected_month'] }}</p>
            </div>
        </button>

        {{-- Dynamic Filtered Valuation --}}
        <div class="group bg-indigo-600 rounded-2xl p-5 flex items-center gap-4 shadow-lg shadow-indigo-100 transition-all text-left relative overflow-hidden text-white">
            <div class="h-12 w-12 rounded-xl bg-white/20 text-white flex items-center justify-center text-2xl shadow-inner">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <p class="text-xs font-black text-indigo-100 uppercase tracking-wider mb-0.5">Active Valuation</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-xs font-bold text-indigo-200">IDR</span>
                    <p class="text-xl font-black leading-none">{{ number_format($this->filteredTotal, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Toolbar - Enterprise Compact --}}
    <div class="bg-white/90 backdrop-blur-xl border border-slate-200/60 rounded-2xl p-3 shadow-sm space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            {{-- Search & Direct Filters --}}
            <div class="flex flex-1 items-center gap-3 min-w-0">
                <div class="relative flex-1 max-w-sm group">
                    <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors text-sm"></i>
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           placeholder="Search PO #, Vendor..." 
                           class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border-transparent rounded-xl text-sm font-bold text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/10 focus:bg-white transition-all shadow-inner">
                </div>

                <div class="flex items-center gap-2">
                    <select wire:model.live="statusFilter"
                            class="bg-slate-50 border-transparent rounded-xl text-xs font-black uppercase tracking-wider text-slate-600 focus:ring-2 focus:ring-indigo-500/10 py-2.5 px-4 transition-all">
                        @foreach($filters['statuses'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="perPage"
                            class="bg-slate-50 border-transparent rounded-xl text-xs font-black uppercase tracking-wider text-slate-600 focus:ring-2 focus:ring-indigo-500/10 py-2.5 px-4 transition-all">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }} Rows</option>
                        @endforeach
                    </select>

                    <button @click="showFilters = !showFilters"
                            :class="showFilters ? 'bg-indigo-600 text-white shadow-md shadow-indigo-100' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="flex items-center gap-2 py-2.5 px-4 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        <i class="bi bi-sliders text-sm"></i>
                        Filters
                    </button>
                </div>
            </div>

            {{-- Create Button --}}
            @if (auth()->user()->department?->name !== 'MANAGEMENT' || auth()->user()->hasRole('super-admin'))
                <button wire:click="enterCreateMode"
                       class="inline-flex items-center gap-2 px-6 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-black uppercase tracking-widest shadow-md hover:bg-indigo-600 transition-all">
                    <i class="bi bi-plus-lg"></i>
                    New PO
                </button>
            @endif
        </div>

        {{-- Advanced Filters (Collapsible Drawer) --}}
        <div x-show="showFilters" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="pt-3 border-t border-slate-100">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Vendor --}}
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-wider ml-1">Vendor</label>
                    <select wire:model.live="vendorFilter"
                            class="w-full bg-slate-50 border-transparent rounded-xl text-xs font-bold text-slate-600 py-2.5 px-4">
                        @foreach($filters['vendors'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date Range --}}
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-wider ml-1">Invoice Period</label>
                    <div class="flex items-center gap-3">
                        <input type="date" wire:model.live="dateFrom" class="flex-1 bg-slate-50 border-transparent rounded-xl text-xs font-bold text-slate-600 py-2.5 px-4">
                        <span class="text-slate-300">-</span>
                        <input type="date" wire:model.live="dateTo" class="flex-1 bg-slate-50 border-transparent rounded-xl text-xs font-bold text-slate-600 py-2.5 px-4">
                    </div>
                </div>

                {{-- Amount Range --}}
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-wider ml-1">Total Valuation (IDR)</label>
                    <div class="flex items-center gap-3">
                        <input type="number" wire:model.live="amountFrom" placeholder="Min" class="flex-1 bg-slate-50 border-transparent rounded-xl text-xs font-bold text-slate-600 py-2.5 px-4">
                        <input type="number" wire:model.live="amountTo" placeholder="Max" class="flex-1 bg-slate-50 border-transparent rounded-xl text-xs font-bold text-slate-600 py-2.5 px-4">
                    </div>
                </div>

                {{-- Category --}}
                <div class="space-y-2">
                    <label class="text-xs font-black text-slate-400 uppercase tracking-wider ml-1">Category</label>
                    <select wire:model.live="categoryFilter"
                            class="w-full bg-slate-50 border-transparent rounded-xl text-xs font-bold text-slate-600 py-2.5 px-4">
                        @foreach($filters['categories'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Filter Pills --}}
    @php
        $activePills = [];
        if ($search) $activePills[] = ['label' => 'Search', 'value' => $search, 'key' => 'search'];
        if ($statusFilter) $activePills[] = ['label' => 'Status', 'value' => $filters['statuses'][$statusFilter], 'key' => 'statusFilter'];
        if ($vendorFilter) $activePills[] = ['label' => 'Vendor', 'value' => $vendorFilter, 'key' => 'vendorFilter'];
        if ($monthFilter) $activePills[] = ['label' => 'Month', 'value' => $filters['months'][$monthFilter] ?? $monthFilter, 'key' => 'monthFilter'];
        if ($dateFrom) $activePills[] = ['label' => 'From', 'value' => $dateFrom, 'key' => 'dateFrom'];
        if ($dateTo) $activePills[] = ['label' => 'To', 'value' => $dateTo, 'key' => 'dateTo'];
        if ($amountFrom) $activePills[] = ['label' => 'Min IDR', 'value' => number_format($amountFrom, 0, ',', '.'), 'key' => 'amountFrom'];
        if ($amountTo) $activePills[] = ['label' => 'Max IDR', 'value' => number_format($amountTo, 0, ',', '.'), 'key' => 'amountTo'];
        if ($creatorFilter) $activePills[] = ['label' => 'Creator', 'value' => $creatorFilter, 'key' => 'creatorFilter'];
        if ($categoryFilter) $activePills[] = ['label' => 'Category', 'value' => $filters['categories'][$categoryFilter] ?? $categoryFilter, 'key' => 'categoryFilter'];
    @endphp

    @if(count($activePills) > 0)
        <div class="flex flex-wrap items-center gap-3 px-1 mb-6">
            <span class="text-xs font-black text-slate-400 uppercase tracking-widest mr-2">Active Criteria:</span>
            @foreach($activePills as $pill)
                <div class="flex items-center gap-2 px-4 py-1.5 bg-white border border-slate-200 rounded-xl shadow-sm hover:border-indigo-200 transition-all group">
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-tight">{{ $pill['label'] }}</span>
                    <span class="text-xs font-black text-slate-700">{{ $pill['value'] }}</span>
                    <button wire:click="$set('{{ $pill['key'] }}', '')" class="text-slate-300 hover:text-rose-500 transition-colors">
                        <i class="bi bi-x-circle-fill text-xs"></i>
                    </button>
                </div>
            @endforeach
            <button wire:click="clearFilters" class="text-xs font-black text-rose-500 uppercase tracking-widest hover:text-rose-600 ml-3 transition-colors">Clear All</button>
        </div>
    @endif

    {{-- Table Section - Compact --}}
    <div class="bg-white border border-slate-200/60 rounded-2xl overflow-hidden shadow-sm relative z-0">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="pl-6 pr-4 py-4 w-12">
                            <input type="checkbox" wire:model.live="selectAll" class="h-5 w-5 rounded border-slate-200 text-indigo-600 focus:ring-indigo-500/10 transition-all">
                        </th>
                        <th class="px-4 py-4">
                            <button type="button" 
                                    wire:click="sortByColumn('po_number')" 
                                    wire:loading.attr="disabled"
                                    class="group flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                                PO Number
                                <i class="bi {{ $sortBy === 'po_number' ? ($sortDirection === 'asc' ? 'bi-sort-up text-indigo-500' : 'bi-sort-down text-indigo-500') : 'bi-arrow-down-up opacity-0 group-hover:opacity-50' }}"></i>
                            </button>
                        </th>
                        <th class="px-4 py-4">
                            <button type="button" 
                                    wire:click="sortByColumn('vendor_name')" 
                                    wire:loading.attr="disabled"
                                    class="group flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                                Vendor
                                <i class="bi {{ $sortBy === 'vendor_name' ? ($sortDirection === 'asc' ? 'bi-sort-up text-indigo-500' : 'bi-sort-down text-indigo-500') : 'bi-arrow-down-up opacity-0 group-hover:opacity-50' }}"></i>
                            </button>
                        </th>
                        {{-- Invoice Info column removed - deprecated fields --}}
                        <th class="px-4 py-4">
                            <button type="button" 
                                    wire:click="sortByColumn('created_at')" 
                                    wire:loading.attr="disabled"
                                    class="group flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                                Creator
                                <i class="bi {{ $sortBy === 'created_at' ? ($sortDirection === 'asc' ? 'bi-sort-up text-indigo-500' : 'bi-sort-down text-indigo-500') : 'bi-arrow-down-up opacity-0 group-hover:opacity-50' }}"></i>
                            </button>
                        </th>
                        <th class="px-4 py-4 hidden lg:table-cell">
                            <button type="button" 
                                    wire:click="sortByColumn('total')" 
                                    wire:loading.attr="disabled"
                                    class="group flex items-center gap-2 text-xs font-black text-slate-400 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                                Valuation
                                <i class="bi {{ $sortBy === 'total' ? ($sortDirection === 'asc' ? 'bi-sort-up text-indigo-500' : 'bi-sort-down text-indigo-500') : 'bi-arrow-down-up opacity-0 group-hover:opacity-50' }}"></i>
                            </button>
                        </th>
                        <th class="px-4 py-4 text-xs font-black text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-8 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($purchaseOrders as $po)
                        @php $isProcessing = in_array($po->id, $processingIds); @endphp
                        <tr class="group hover:bg-slate-50/50 transition-all {{ $isProcessing ? 'opacity-40 grayscale pointer-events-none' : '' }}">
                            <td class="pl-6 pr-4 py-4">
                                <input type="checkbox" wire:model.live="selectedIds" value="{{ $po->id }}" class="h-5 w-5 rounded border-slate-200 text-indigo-600 focus:ring-indigo-500/10 transition-all">
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors cursor-pointer" wire:click="openDetailModal({{ $po->id }})">
                                        {{ $po->po_number }}
                                    </span>
                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">{{ $po->category?->name ?? 'Uncategorized' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-800 truncate max-w-[180px]">{{ $po->vendor_name }}</span>
                                </div>
                            </td>
                            {{-- Invoice Info column removed - deprecated fields --}}
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-lg bg-slate-100 flex items-center justify-center text-xs font-black text-slate-500 shadow-inner">
                                        {{ mb_substr($po->user?->name ?? '?', 0, 1) }}
                                    </div>
                                    <div class="flex flex-col leading-tight">
                                        <span class="text-xs font-bold text-slate-700">{{ $po->user?->name ?: 'System' }}</span>
                                        <div class="flex items-center gap-2 text-[10px] font-medium text-slate-400 uppercase tracking-tighter">
                                            <span>{{ $po->created_at->format('d M Y') }}</span>
                                            <span class="opacity-30">•</span>
                                            <span>{{ $po->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 hidden lg:table-cell font-mono text-sm font-black text-slate-900">
                                <span class="text-xs text-slate-400 mr-1">IDR</span>{{ number_format($po->total, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-col gap-2">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black uppercase tracking-wider {{ $po->getStatusEnum()->cssClass() }}">
                                            {{ $isProcessing ? 'Processing' : $po->getStatusEnum()->label() }}
                                        </span>
                                        @if($po->workflow_status === 'IN_REVIEW')
                                            @php $daysPending = now()->diffInDays($po->approvalRequest?->submitted_at ?? $po->created_at); @endphp
                                            @if($daysPending > 0)
                                                <span class="text-xs font-black {{ $daysPending > 3 ? 'text-rose-500' : 'text-amber-500' }} flex items-center gap-1.5" title="Days in Review">
                                                    <i class="bi bi-clock-history"></i>
                                                    {{ $daysPending }}D
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                    @if($po->workflow_status === 'IN_REVIEW')
                                        <div class="flex flex-col gap-1.5">
                                            <span class="text-[10px] font-bold text-slate-400 truncate max-w-[120px]">
                                                @if($po->workflow_step)
                                                    {{ Str::limit($po->workflow_step, 15) }}
                                                @else
                                                    Preparing
                                                @endif
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-8 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openDetailModal({{ $po->id }})" class="p-2 bg-slate-50 text-slate-400 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-all" title="View">
                                        <i class="bi bi-eye text-base"></i>
                                    </button>
                                    @if($po->getStatusEnum()->canEdit())
                                        <button wire:click="enterEditMode" class="p-2 bg-slate-50 text-slate-400 rounded-lg hover:bg-amber-50 hover:text-amber-600 transition-all" title="Edit">
                                            <i class="bi bi-pencil text-base"></i>
                                        </button>
                                    @endif
                                    <a href="{{ route('po.view', $po->id) }}" class="p-2 bg-slate-50 text-slate-400 rounded-lg hover:bg-slate-900 hover:text-white transition-all" title="Open">
                                        <i class="bi bi-box-arrow-up-right text-base"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <div class="h-12 w-12 rounded-2xl bg-slate-50 flex items-center justify-center text-2xl text-slate-200">
                                        <i class="bi bi-inbox"></i>
                                    </div>
                                    <p class="text-sm font-black text-slate-900">No Records Found</p>
                                    <button wire:click="clearFilters" class="text-[9px] font-black text-indigo-600 uppercase tracking-widest hover:underline">Reset Filters</button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Footer with pagination and export --}}
        <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-4">
                @if($purchaseOrders->total() > 0)
                    <div class="text-sm text-slate-600">
                        {{ $purchaseOrders->total() }} result{{ $purchaseOrders->total() !== 1 ? 's' : '' }}
                        @if($purchaseOrders->hasPages())
                            <span class="text-slate-400">•</span>
                            Page {{ $purchaseOrders->currentPage() }} of {{ $purchaseOrders->lastPage() }}
                        @endif
                    </div>
                    <button wire:click="exportFiltered"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export
                    </button>
                @endif
            </div>

            @if ($purchaseOrders->hasPages())
                <div class="flex items-center gap-1">
                    @if ($purchaseOrders->onFirstPage())
                        <button disabled class="px-2.5 py-1.5 text-sm border border-slate-200 rounded-md bg-slate-50 text-slate-400 cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                    @else
                        <button wire:click.prevent="setPage({{ $purchaseOrders->currentPage() - 1 }})"
                                class="px-2.5 py-1.5 text-sm border border-slate-200 rounded-md hover:bg-white transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                    @endif

                    <div class="flex gap-1">
                        @foreach ($purchaseOrders->getUrlRange(max(1, $purchaseOrders->currentPage() - 2), min($purchaseOrders->lastPage(), $purchaseOrders->currentPage() + 2)) as $page => $url)
                            <button wire:click.prevent="setPage({{ $page }})"
                                    class="px-3 py-1.5 text-sm border rounded-md transition-colors {{ $page == $purchaseOrders->currentPage() ? 'bg-indigo-600 text-white border-indigo-600' : 'border-slate-200 hover:bg-white' }}">
                                {{ $page }}
                            </button>
                        @endforeach
                    </div>

                    @if ($purchaseOrders->hasMorePages())
                        <button wire:click.prevent="setPage({{ $purchaseOrders->currentPage() + 1 }})"
                                class="px-2.5 py-1.5 text-sm border border-slate-200 rounded-md hover:bg-white transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    @else
                        <button disabled class="px-2.5 py-1.5 text-sm border border-slate-200 rounded-md bg-slate-50 text-slate-400 cursor-not-allowed">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Floating Bulk Action Bar - Compact & Fixed Bug --}}
    @role('director')
        <template x-teleport="body">
            <div x-show="selectedIds.length > 0"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-10 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                class="fixed bottom-6 left-1/2 -translate-x-1/2 z-50 flex items-center gap-2 p-1.5 bg-slate-900/95 backdrop-blur-xl rounded-2xl shadow-xl border border-white/10 overflow-hidden">
                
                <div class="px-4 py-1.5 border-r border-white/10">
                    <span class="text-[10px] font-black text-white uppercase tracking-widest">
                        <span x-text="selectedIds.length" class="text-indigo-400"></span> Selected
                    </span>
                </div>

                <div class="flex items-center gap-1">
                    @if($this->bulkActionReason)
                        <div class="flex items-center gap-1.5 px-3 py-1 bg-rose-500/10 rounded-xl mr-1 border border-rose-500/20">
                            <i class="bi bi-info-circle-fill text-rose-400 text-[10px]"></i>
                            <span class="text-[8px] font-black text-rose-300 uppercase tracking-tight">{{ $this->bulkActionReason }}</span>
                        </div>
                    @endif
                    <button wire:click="approveSelected"
                            @if(!$this->canBulkAction) disabled @endif
                            class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-wider transition-all
                                {{ $this->canBulkAction ? 'bg-indigo-500 text-white hover:bg-indigo-400' : 'bg-white/5 text-white/30 cursor-not-allowed' }}">
                        Approve
                    </button>
                    <button @click="$dispatch('open-reject-modal')"
                            @if(!$this->canBulkAction) disabled @endif
                            class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-wider transition-all
                                {{ $this->canBulkAction ? 'bg-rose-500 text-white hover:bg-rose-400' : 'bg-white/5 text-white/30 cursor-not-allowed' }}">
                        Reject
                    </button>
                    <button @click="$wire.selectedIds = []; $wire.selectAll = false;" 
                            class="p-2 text-white/40 hover:text-white rounded-xl transition-all">
                        <i class="bi bi-x-octagon text-xs"></i>
                    </button>
                </div>
            </div>
        </template>
    @endrole

    
    {{-- Purchase Order Detail Modal --}}
    <template x-teleport="body">
        <div x-data="{
                open: @entangle('showDetailModal').live,
                loading: @entangle('modalLoading').live
            }"
             x-show="open"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             x-on:keydown.escape.window="$wire.closeDetailModal()">

            <div class="flex items-end justify-center min-h-screen pt-4 px-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" x-on:click="$wire.closeDetailModal()"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                <div class="max-h-screen overflow-y-auto">
                    {{-- Header --}}
                    <div class="bg-white px-4 py-3 sm:px-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Purchase Order: {{ $selectedPurchaseOrder?->po_number ?? 'N/A' }}
                                </h3>
                                @if($selectedPurchaseOrder)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $selectedPurchaseOrder->getStatusEnum()->cssClass() }} ml-3">
                                        {{ $selectedPurchaseOrder->getStatusEnum()->label() }}
                                    </span>
                                @endif
                            </div>
                            <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="font-bold text-xs">#{{ $selectedPurchaseOrder?->id ?? 'N/A' }}</div>
                    </div>

                    {{-- Content --}}
                    <div class="px-4 py-4 sm:p-5">
                        {{-- Loading State --}}
                        <div x-show="loading" x-transition class="flex items-center justify-center py-12">
                            <div class="text-center">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto mb-4"></div>
                                <p class="text-sm text-gray-600">Loading purchase order details...</p>
                            </div>
                        </div>

                        {{-- Purchase Order Content --}}
                        <div x-show="!loading" x-transition>
                            @if($selectedPurchaseOrder)
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[80vh]">
                                    {{-- Left Side: PDF Viewer (75% width on desktop) --}}
                                    <div class="lg:col-span-8 flex flex-col h-full bg-slate-100 rounded-xl overflow-hidden border border-slate-200">
                                        <div class="px-4 py-2 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                                            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Document Preview</span>
                                            <a href="{{ $pdfUrl }}" target="_blank" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">
                                                <i class="bi bi-box-arrow-up-right"></i> Open External
                                            </a>
                                        </div>
                                        @if($pdfUrl)
                                            <iframe src="{{ $pdfUrl }}#toolbar=0"
                                                    class="flex-1 w-full border-0"
                                                    title="Purchase Order PDF">
                                            </iframe>
                                        @else
                                            <div class="flex-1 flex items-center justify-center text-slate-400 italic text-sm">
                                                No PDF document available for this PO.
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Right Side: Quick Action Sidebar (25% width on desktop) --}}
                                    <div class="lg:col-span-4 flex flex-col h-full space-y-5">
                                        {{-- Verification Checklist --}}
                                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 space-y-3">
                                            <div>
                                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Vendor</label>
                                                <p class="text-sm font-bold text-slate-800">{{ $selectedPurchaseOrder->vendor_name }}</p>
                                            </div>
                                            <div>
                                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Category</label>
                                                <p class="text-sm font-bold text-slate-800">{{ $selectedPurchaseOrder->category->name }}</p>
                                            </div>
                                            {{-- Invoice fields removed - deprecated --}}
                                            <div class="pt-3 border-t border-slate-100">
                                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Amount</label>
                                                <p class="text-xl font-black text-slate-900">
                                                    <span class="text-xs text-slate-400 mr-1">{{ $selectedPurchaseOrder->currency }}</span>
                                                    {{ number_format($selectedPurchaseOrder->total, 0, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>

                                        {{-- Rejection Remarks (Most Important) --}}
                                        @if($selectedPurchaseOrder->getStatusEnum()->label() === 'Rejected')
                                            @php
                                                $latestRejection = $selectedPurchaseOrder->approvalRequest?->actions
                                                    ?->where('to_status', 'REJECTED')
                                                    ->sortByDesc('created_at')
                                                    ->first();
                                            @endphp
                                            
                                            @if($latestRejection && $latestRejection->remarks)
                                                <div class="p-4 bg-rose-50 rounded-2xl border border-rose-100">
                                                    <p class="text-[10px] font-black text-rose-400 uppercase tracking-widest flex items-center gap-1">
                                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                                        Rejection Reason
                                                    </p>
                                                    <p class="text-xs font-bold text-rose-700 mt-1 italic leading-relaxed">
                                                        "{{ $latestRejection->remarks }}"
                                                    </p>
                                                    <div class="flex items-center justify-between mt-2">
                                                        <p class="text-[9px] font-bold text-rose-400">
                                                            By {{ $latestRejection->causer->name ?? 'System' }}
                                                        </p>
                                                        <p class="text-[9px] text-rose-300 italic">
                                                            {{ $latestRejection->created_at->diffForHumans() }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif

                                        {{-- Workflow Info --}}
                                        @if($selectedPurchaseOrder->workflow_status === 'IN_REVIEW')
                                            <div class="p-4 bg-indigo-50 rounded-2xl border border-indigo-100">
                                                <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest">Current Step</p>
                                                <p class="text-xs font-bold text-indigo-700 mt-1">Waiting for {{ $selectedPurchaseOrder->workflow_step ?: 'Approver' }}</p>
                                            </div>
                                        @endif

                                        {{-- Action Buttons --}}
                                        <div class="flex flex-col gap-3 mt-auto">
                                            @if($this->canApproveSelectedPO())
                                                <button wire:click="approvePurchaseOrder"
                                                        class="w-full py-3.5 bg-indigo-600 text-white rounded-2xl font-black shadow-lg shadow-indigo-100 hover:bg-indigo-700 hover:scale-[1.02] transition-all">
                                                    Approve & Sign
                                                </button>
                                            @endif

                                            @if($this->canRejectSelectedPO())
                                                <button wire:click="$dispatch('open-reject-modal')"
                                                        class="w-full py-3 bg-white text-rose-600 border border-rose-200 rounded-2xl font-bold hover:bg-rose-50 transition-all">
                                                    Reject
                                                </button>
                                            @endif

                                            <div class="grid grid-cols-2 gap-3 pt-2">
                                                <a href="{{ route('po.view', $selectedPurchaseOrder->id) }}"
                                                   class="flex items-center justify-center py-2.5 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-200 transition-all">
                                                    Full Page
                                                </a>
                                                <button wire:click="closeDetailModal"
                                                        type="button"
                                                        class="flex items-center justify-center py-2.5 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold hover:bg-slate-200 transition-all">
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
    
    {{-- Reject Modal - Compact --}}
    <template x-teleport="body">
        <div x-data="{ open: false, reason: '' }" 
            x-show="open" 
            x-cloak
            x-on:open-reject-modal.window="open = true"
            x-on:close-reject-modal.window="open = false; reason = ''"
            class="fixed inset-0 z-[120] flex items-center justify-center p-4">
            
            <div x-show="open" x-transition.opacity class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="open = false"></div>

            <div x-show="open" 
                 x-transition:enter="ease-out duration-200" 
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4" 
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="relative w-full max-w-sm rounded-2xl bg-white shadow-2xl p-6 space-y-6 text-center border border-slate-200/50">
                
                <div class="h-16 w-16 bg-rose-50 rounded-2xl flex items-center justify-center text-rose-500 mx-auto">
                    <i class="bi bi-exclamation-octagon text-3xl"></i>
                </div>

                <div class="space-y-1">
                    <h3 class="text-xl font-black text-slate-900">Confirm Rejection</h3>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Feedback is required for rejection</p>
                </div>

                <textarea x-model="reason" 
                          rows="3" 
                          class="w-full rounded-xl border-slate-100 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-rose-500/10 text-xs p-3 transition-all placeholder:text-slate-300"
                          placeholder="Why is this being rejected?"></textarea>

                <div class="flex flex-col gap-2">
                    <button @click="$wire.rejectSelected(reason); open = false" 
                            :disabled="!reason || reason.length < 3"
                            class="w-full py-3 bg-rose-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-rose-700 transition-all disabled:opacity-50">
                        Confirm Rejection
                    </button>
                    <button @click="open = false" 
                            class="w-full py-2 text-[9px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-600 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </template>

    @elseif($formMode === 'create')
        {{-- Full-screen create form --}}
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                {{-- Breadcrumb Navigation --}}
                <nav class="flex mb-8" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <button wire:click="exitFormMode"
                                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors">
                                <i class="bi bi-house-door mr-2"></i>
                                Purchase Orders
                            </button>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="bi bi-chevron-right text-gray-400 text-sm mx-1"></i>
                                <span class="text-sm font-medium text-gray-500">Create Purchase Order</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                {{-- Page Header --}}
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Create New Purchase Order</h1>
                    <p class="mt-2 text-sm text-gray-600">Fill in the details below to create a new purchase order.</p>
                </div>

                {{-- Create form --}}
                @livewire('purchase-order.create-purchase-order-form')
            </div>
        </div>

    @elseif($formMode === 'edit')
        {{-- Full-screen edit form --}}
        <div class="min-h-screen bg-gray-50 py-8">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                {{-- Breadcrumb Navigation --}}
                <nav class="flex mb-8" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <button wire:click="exitFormMode"
                                    class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors">
                                <i class="bi bi-house-door mr-2"></i>
                                Purchase Orders
                            </button>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <i class="bi bi-chevron-right text-gray-400 text-sm mx-1"></i>
                                <span class="text-sm font-medium text-gray-500">Edit Purchase Order</span>
                            </div>
                        </li>
                    </ol>
                </nav>

                {{-- Page Header --}}
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900">Edit Purchase Order</h1>
                    <p class="mt-2 text-sm text-gray-600">Update the purchase order details below.</p>
                </div>

                {{-- Edit form --}}
                @livewire('purchase-order.edit-purchase-order-form', ['poId' => $editingPo?->id])
            </div>
        </div>
    @endif
</div>
