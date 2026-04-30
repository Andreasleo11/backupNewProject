
<div @if(!empty($processingIds)) wire:poll.3s="checkProcessingStatus" @endif>
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
                            @php
                                $isProcessing = in_array($po->id, $processingIds);
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors {{ $isProcessing ? 'opacity-50 pointer-events-none bg-slate-50' : '' }}">
                                <td class="px-4 py-2.5">
                                    <input type="checkbox" value="{{ $po->id }}" wire:model.live="selectedIds"
                                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="font-medium text-slate-900">{{ $po->po_number }}</span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="font-medium">{{ $po->vendor_name }}</span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex flex-col gap-2">
                                        <span class="text-xs text-slate-500">{{ $po->user?->name ?: 'Unknown' }}</span>
                                        <span class="text-xs text-slate-500">{{ $po->created_at->diffForHumans() }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 hidden md:table-cell">
                                    <div class="flex flex-col gap-1.5">
                                        <span class="inline-flex items-center w-fit px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $po->getStatusEnum()->cssClass() }}"
                                                title="{{ $po->getStatusEnum()->description() }}">
                                            @if($isProcessing)
                                                <i class="bi bi-arrow-repeat animate-spin mr-1.5"></i>
                                                Signing...
                                            @else
                                                {{ $po->getStatusEnum()->label() }}
                                                @if($po->getStatusEnum()->isPendingApproval())
                                                    <span class="ml-1.5 w-1.5 h-1.5 bg-orange-400 rounded-full animate-pulse"></span>
                                                @endif
                                            @endif
                                        </span>
                                        
                                        @if($po->workflow_status === 'IN_REVIEW' && $po->current_approver)
                                            <span class="text-[10px] font-medium text-slate-400 italic">
                                                With: {{ $po->current_approver }}
                                            </span>
                                        @elseif($po->approved_date)
                                            <span class="text-[10px] text-slate-400">
                                                Approved {{ $po->approved_date->diffForHumans() }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 font-mono text-slate-900 hidden lg:table-cell">
                                    {{ number_format($po->total, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2.5 text-slate-600 hidden xl:table-cell">
                                    {{ $po->tanggal_pembayaran ? $po->tanggal_pembayaran->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex gap-1">
                                        <button wire:click="openDetailModal({{ $po->id }})"
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 border border-indigo-200 rounded hover:bg-indigo-100 transition-colors"
                                                title="Quick view">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                            </svg>
                                        </button>
                                        <a href="{{ route('po.view', $po->id) }}"
                                           class="inline-flex items-center px-2 py-1 text-xs font-medium text-slate-600 bg-slate-50 border border-slate-200 rounded hover:bg-slate-100 transition-colors"
                                           title="Full page view">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </a>
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
                                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Invoice Date</label>
                                                <p class="text-sm font-bold text-slate-800">{{ $selectedPurchaseOrder->invoice_date ? $selectedPurchaseOrder->invoice_date->format('d M Y') : '-' }}</p>
                                            </div>
                                            <div class="pt-3 border-t border-slate-100">
                                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Amount</label>
                                                <p class="text-xl font-black text-slate-900">
                                                    <span class="text-xs text-slate-400 mr-1">{{ $selectedPurchaseOrder->currency }}</span>
                                                    {{ number_format($selectedPurchaseOrder->total, 0, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>

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
                                                    Approve PO
                                                </button>
                                            @endif

                                            @if($this->canRejectSelectedPO())
                                                <button wire:click="$dispatch('open-reject-modal')"
                                                        class="w-full py-3 bg-white text-rose-600 border border-rose-200 rounded-2xl font-bold hover:bg-rose-50 transition-all">
                                                    Reject Order
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
    
    {{-- Reject Modal --}}
    <template x-teleport="body">
        <div x-data="{ open: false, reason: '' }" 
            x-show="open" 
            x-cloak
            x-on:open-reject-modal.window="open = true"
            x-on:close-reject-modal.window="open = false"
            class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                    <div class="absolute inset-0 bg-gray-500 opacity-75" x-on:click="open = false"></div>
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
                                    <textarea id="reject-reason" x-model="reason"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            rows="3" placeholder="Enter rejection reason..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button type="button" x-on:click="$wire.rejectSelected(reason); open = false"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                                wire:loading.attr="disabled" wire:target="rejectSelected">
                            <span wire:loading.remove wire:target="rejectSelected">Reject</span>
                            <span wire:loading wire:target="rejectSelected">Rejecting...</span>
                        </button>
                        <button type="button" x-on:click="open = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                                wire:loading.attr="disabled">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
