
<div>
    {{-- Loading Overlay --}}
    <div wire:loading.delay class="fixed inset-0 bg-slate-900 bg-opacity-50 flex items-center justify-center z-50" wire:target="approveSelected,rejectSelected,exportSelected,exportFiltered">
        <div class="bg-white rounded-lg p-6 flex items-center gap-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"></div>
            <span class="text-slate-700">Processing...</span>
        </div>
    </div>

    {{-- Page header --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 tracking-tight">
                Purchase Orders
            </h1>

            {{-- Breadcrumb --}}
            <nav class="mt-2" aria-label="Breadcrumb">
                <ol class="flex items-center gap-1 text-sm text-slate-500">
                    <li>
                        <a href="{{ route('po.dashboard') }}" class="hover:text-slate-700">
                            Dashboard
                        </a>
                    </li>
                    <li class="px-1 text-slate-400">/</li>
                    <li class="text-slate-700 font-medium">
                        List
                    </li>
                </ol>
            </nav>
        </div>

        @if (auth()->user()->department?->name !== 'MANAGEMENT' || auth()->user()->hasRole('super-admin'))
            <div class="flex justify-end">
                <a href="{{ route('po.create') }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2">
                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-md bg-indigo-500/80 text-xs font-bold">
                        +
                    </span>
                    <span>New Purchase Order</span>
                </a>
            </div>
        @endif
    </div>

    {{-- Controls Bar --}}
    <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-xl mb-4"
         x-data="{ showAdvancedFilters: false }">
        <div class="px-4 py-3 border-b border-slate-100">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                {{-- Search & Quick Filters --}}
                <div class="flex flex-col sm:flex-row gap-3 flex-1">
                    <div class="flex-1 max-w-md">
                        <label for="search" class="sr-only">Search</label>

                        <div class="relative">
                            <!-- Search Icon -->
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>

                            <!-- Input -->
                            <input
                                type="text"
                                id="search"
                                wire:model.debounce.300ms="search"
                                placeholder="Search PO number, vendor, invoice..."
                                class="block w-full px-3 py-2 pl-10 pr-10 text-sm border rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                            />

                            <!-- Loading Spinner -->
                            <div wire:loading.delay wire:target="search" class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <div class="w-4 h-4 border-2 border-slate-400 border-t-transparent rounded-full animate-spin"></div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <select wire:model.live="statusFilter"
                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($filters['statuses'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>

                        <select wire:model.live="vendorFilter"
                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($filters['vendors'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    {{-- Bulk Actions --}}
                    @if(count($selectedIds) > 0)
                        <div class="flex gap-1">
                            <button wire:click="approveSelected"
                                    class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-green-700 bg-green-50 border border-green-200 rounded-md hover:bg-green-100">
                                Approve ({{ count($selectedIds) }})
                            </button>
                            <button wire:click="$dispatch('open-reject-modal')"
                                    class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded-md hover:bg-red-100">
                                Reject ({{ count($selectedIds) }})
                            </button>
                        </div>
                    @endif

                    {{-- Advanced Filters Toggle --}}
                    <button @click="showAdvancedFilters = !showAdvancedFilters"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-600 bg-slate-100 border border-slate-300 rounded-lg hover:bg-slate-200 transition-colors">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Filters
                        <svg class="w-4 h-4 ml-1 transition-transform" :class="{ 'rotate-180': showAdvancedFilters }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Advanced Filters (Collapsible) --}}
            <div x-show="showAdvancedFilters"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 max-h-0"
                 x-transition:enter-end="opacity-100 max-h-96"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 max-h-96"
                 x-transition:leave-end="opacity-0 max-h-0"
                 class="mt-4 pt-4 border-t border-slate-100 overflow-hidden">

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Date Range --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Invoice Date Range</label>
                        <div class="space-y-2">
                            <input type="date" wire:model.live.debounce.300ms="dateFrom"
                                   class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="date" wire:model.live.debounce.300ms="dateTo"
                                   class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Amount Range --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Amount Range (IDR)</label>
                        <div class="space-y-2">
                            <input type="number" wire:model.live.debounce.300ms="amountFrom" placeholder="Minimum"
                                   class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="number" wire:model.live.debounce.300ms="amountTo" placeholder="Maximum"
                                   class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Creator & Month --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Creator</label>
                        <select wire:model.live="creatorFilter"
                                class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($filters['creators'] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Month & Per Page --}}
                    <div class="space-y-2">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Month</label>
                            <select wire:model.live="monthFilter"
                                    class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($filters['months'] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2 items-end">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-slate-600 mb-1">Rows</label>
                                <select wire:model.live="perPage"
                                        class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($perPageOptions as $option)
                                        <option value="{{ $option }}">{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button wire:click="clearFilters"
                                    class="px-3 py-2 text-sm font-medium text-slate-600 bg-slate-100 border border-slate-300 rounded-lg hover:bg-slate-200">
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-700">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 w-12">
                                <input type="checkbox" wire:model.live="selectAll"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </th>
                            <th class="px-4 py-3 font-medium">
                                <button wire:click="sortBy('po_number')" class="flex items-center gap-1 hover:text-slate-900">
                                    PO Details
                                    @if($sortBy === 'po_number')
                                        <svg class="w-4 h-4 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 font-medium hidden sm:table-cell">Invoice Info</th>
                            <th class="px-4 py-3 font-medium">Vendor</th>
                            <th class="px-4 py-3 font-medium">Creator</th>
                            <th class="px-4 py-3 font-medium hidden md:table-cell">Status</th>
                            <th class="px-4 py-3 font-medium hidden lg:table-cell">
                                <button wire:click="sortBy('total')" class="flex items-center gap-1 hover:text-slate-900">
                                    Amount
                                    @if($sortBy === 'total')
                                        <svg class="w-4 h-4 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 font-medium hidden xl:table-cell">
                                <button wire:click="sortBy('tanggal_pembayaran')" class="flex items-center gap-1 hover:text-slate-900">
                                    Payment Date
                                    @if($sortBy === 'tanggal_pembayaran')
                                        <svg class="w-4 h-4 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-4 py-3 font-medium w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($purchaseOrders as $po)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3">
                                    <input type="checkbox" value="{{ $po->id }}" wire:model.live="selectedIds"
                                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-slate-900">{{ $po->po_number }}</span>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    <div class="flex flex-col text-sm">
                                        <div class="flex items-center gap-2">
                                            <span class="text-slate-600 text-xs">Date:</span>
                                            <span class="font-medium text-xs">{{ $po->invoice_date ? $po->invoice_date->format('d/m/Y') : '-' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-slate-600 text-xs">No:</span>
                                            <span class="font-medium whitespace-nowrap text-xs">{{ $po->invoice_number ?: '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="font-medium">{{ $po->vendor_name }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col gap-2">
                                        <span class="text-xs text-slate-500">{{ $po->user?->name ?: 'Unknown' }}</span>
                                        <span class="text-xs text-slate-500">{{ $po->created_at->diffForHumans() }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $po->getStatusEnum()->cssClass() }}"
                                            title="{{ $po->getStatusEnum()->description() }}">
                                        {{ $po->getStatusEnum()->label() }}
                                        @if($po->getStatusEnum()->isPendingApproval())
                                            <span class="ml-1 w-2 h-2 bg-orange-400 rounded-full animate-pulse" title="Awaiting approval"></span>
                                        @endif
                                    </span>
                                    
                                    @if($po->approved_date)
                                        <div class="text-xs text-slate-500 mt-1">{{ $po->approved_date->diffForHumans() }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-slate-900 hidden lg:table-cell">
                                    {{ number_format($po->total, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-slate-600 hidden xl:table-cell">
                                    {{ $po->tanggal_pembayaran ? $po->tanggal_pembayaran->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-1">
                                        <button wire:click="openDetailModal({{ $po->id }})"
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded hover:bg-indigo-100 transition-colors"
                                                title="View details">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        @if($po->getStatusEnum()->canEdit())
                                            <a href="{{ route('po.edit', $po->id) }}"
                                               class="inline-flex items-center px-2 py-1 text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded hover:bg-slate-100 transition-colors"
                                               title="Edit">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                          @empty
                              <tr>
                                  <td colspan="8" class="px-4 py-12 text-center text-slate-500">
                                      <div class="flex flex-col items-center">
                                          <svg class="h-12 w-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                          </svg>
                                          <p class="text-sm font-medium">No purchase orders found</p>
                                          <p class="text-xs mt-1">Try adjusting your filters or create a new purchase order.</p>
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
    </div>

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

            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" x-on:click="$wire.closeDetailModal()"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                <div class="max-h-screen overflow-y-auto">
                    {{-- Header --}}
                    <div class="bg-white px-4 py-5 sm:px-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Purchase Order #{{ $selectedPurchaseOrder?->po_number ?? 'N/A' }}
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
                    </div>

                    {{-- Content --}}
                    <div class="px-4 py-5 sm:p-6">
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
                                <div class="space-y-6">
                                    {{-- Basic Information --}}
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <h4 class="text-sm font-medium text-gray-900 mb-3">Basic Information</h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">PO Number</dt>
                                                <dd class="mt-1 text-sm text-gray-900">{{ $selectedPurchaseOrder->po_number }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Vendor</dt>
                                                <dd class="mt-1 text-sm text-gray-900">{{ $selectedPurchaseOrder->vendor_name }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Invoice Date</dt>
                                                <dd class="mt-1 text-sm text-gray-900">{{ $selectedPurchaseOrder->invoice_date ? $selectedPurchaseOrder->invoice_date->format('M d, Y') : '-' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Invoice Number</dt>
                                                <dd class="mt-1 text-sm text-gray-900">{{ $selectedPurchaseOrder->invoice_number ?: '-' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Amount</dt>
                                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $selectedPurchaseOrder->total ? 'Rp ' . number_format($selectedPurchaseOrder->total, 0, ',', '.') : '-' }}</dd>
                                            </div>
                                            <div>
                                                <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Payment Date</dt>
                                                <dd class="mt-1 text-sm text-gray-900">{{ $selectedPurchaseOrder->tanggal_pembayaran ? $selectedPurchaseOrder->tanggal_pembayaran->format('M d, Y') : '-' }}</dd>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Approval Information --}}
                                    @if($selectedPurchaseOrder->approvalRequest)
                                        <div class="bg-blue-50 rounded-lg p-4">
                                            <h4 class="text-sm font-medium text-gray-900 mb-3">Approval Status</h4>
                                            <div class="space-y-2">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-sm text-gray-600">Current Status:</span>
                                                    <span class="text-sm font-medium">{{ $selectedPurchaseOrder->workflow_status }}</span>
                                                </div>
                                                @if($selectedPurchaseOrder->workflow_step)
                                                    <div class="flex items-center justify-between">
                                                        <span class="text-sm text-gray-600">Current Approver:</span>
                                                        <span class="text-sm font-medium">{{ $selectedPurchaseOrder->workflow_step }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    {{-- PDF Viewer --}}
                                    @if($pdfUrl)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-3">
                                                <h4 class="text-sm font-medium text-gray-900">Purchase Order PDF</h4>
                                                <button wire:click="downloadPdf"
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded hover:bg-indigo-100">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    Download
                                                </button>
                                            </div>
                                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                                <iframe src="{{ $pdfUrl }}"
                                                        class="w-full h-96 border-0"
                                                        title="Purchase Order PDF">
                                                    <p class="p-4 text-center text-gray-500">
                                                        Your browser does not support PDFs.
                                                        <a href="{{ $pdfUrl }}" target="_blank" class="text-indigo-600 hover:text-indigo-500">Click here to view the PDF</a>
                                                    </p>
                                                </iframe>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Actions --}}
                                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                                        @if($this->canApproveSelectedPO())
                                            <button wire:click="approvePurchaseOrder"
                                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                Approve
                                            </button>
                                        @endif

                                        @if($this->canRejectSelectedPO())
                                            <button wire:click="$dispatch('open-reject-modal')"
                                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                Reject
                                            </button>
                                        @endif

                                        @if($this->canEditSelectedPO())
                                            <button wire:click="editPurchaseOrder"
                                                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Edit
                                            </button>
                                        @endif

                                        <button wire:click="closeDetailModal"
                                                type="button"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            Close
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div x-data="{ open: false }" x-show="open" x-cloak
        x-on:open-reject-modal.window="open = true"
        x-on:close-reject-modal.window="open = false"
        class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                <div>
                    <div class="mt-3 text-center sm:mt-0 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Reject Purchase Orders
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to reject {{ count($selectedIds) }} selected purchase order(s)?
                                This action cannot be undone.
                            </p>
                            <div class="mt-4">
                                <label for="reject-reason" class="block text-sm font-medium text-gray-700">
                                    Rejection Reason
                                </label>
                                <textarea id="reject-reason" x-data="{ reason: '' }" x-model="reason"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        rows="3" placeholder="Enter rejection reason..."></textarea>
                </div>
            </div>
        </div>
    </div>
    </template>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button type="button" x-on:click="rejectSelected(reason); open = false"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Reject
                    </button>
                    <button type="button" x-on:click="open = false"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>