
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

    {{-- Table shell --}}
    <div class="bg-white shadow-sm ring-1 ring-slate-200 rounded-xl">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <div>
                <h2 class="text-sm font-semibold text-slate-800">
                    Purchase Order List
                </h2>
                <p class="mt-0.5 text-xs text-slate-500">
                    Use filters and search to narrow down by status, vendor, or period.
                </p>
            </div>

            {{-- Bulk Actions --}}
            <div class="flex gap-2">
                @if(count($selectedIds) > 0)
                    <button wire:click="approveSelected"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100">
                        Approve Selected ({{ count($selectedIds) }})
                    </button>
                    <button wire:click="$dispatch('open-reject-modal')"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">
                        Reject Selected ({{ count($selectedIds) }})
                    </button>
                    <button wire:click="exportSelected"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100">
                        Export Selected ({{ count($selectedIds) }})
                    </button>
                @else
                    <button wire:click="exportFiltered"
                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100">
                        Export Filtered
                    </button>
                @endif
            </div>
        </div>

        {{-- Filters --}}
        <div class="px-4 py-3 border-b border-slate-100">
            {{-- Basic Filters --}}
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                {{-- Search --}}
                <div>
                    <label for="search" class="block text-xs font-medium text-slate-600 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" id="search" wire:model.live.debounce.300ms="search"
                               placeholder="PO number, vendor, invoice..."
                               class="block w-full rounded-lg border border-slate-300 px-3 py-2 pr-8 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <div wire:loading wire:target="search" class="absolute right-2 top-2">
                            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-slate-400"></div>
                        </div>
                    </div>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label for="statusFilter" class="block text-xs font-medium text-slate-600 mb-1">Status</label>
                    <select id="statusFilter" wire:model.live="statusFilter"
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($filters['statuses'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Vendor Filter --}}
                <div>
                    <label for="vendorFilter" class="block text-xs font-medium text-slate-600 mb-1">Vendor</label>
                    <select id="vendorFilter" wire:model.live="vendorFilter"
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($filters['vendors'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Month Filter --}}
                <div>
                    <label for="monthFilter" class="block text-xs font-medium text-slate-600 mb-1">Month</label>
                    <select id="monthFilter" wire:model.live="monthFilter"
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($filters['months'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Rows Per Page --}}
                <div>
                    <label for="perPage" class="block text-xs font-medium text-slate-600 mb-1">Rows</label>
                    <select id="perPage" wire:model.live="perPage"
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Advanced Filters --}}
            <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-6 gap-4">
                {{-- Date Range --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Date Range</label>
                    <div class="flex gap-2">
                        <input type="date" wire:model.live.debounce.300ms="dateFrom"
                               class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="text-slate-400 self-center">to</span>
                        <input type="date" wire:model.live.debounce.300ms="dateTo"
                               class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Amount Range --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Amount Range</label>
                    <div class="flex gap-2">
                        <input type="number" wire:model.live.debounce.300ms="amountFrom" placeholder="Min"
                               class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="text-slate-400 self-center">to</span>
                        <input type="number" wire:model.live.debounce.300ms="amountTo" placeholder="Max"
                               class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                {{-- Creator Filter --}}
                <div>
                    <label for="creatorFilter" class="block text-xs font-medium text-slate-600 mb-1">Creator</label>
                    <select id="creatorFilter" wire:model.live="creatorFilter"
                            class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($filters['creators'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Clear Filters --}}
                <div class="flex items-end">
                    <button wire:click="clearFilters"
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-slate-600 bg-slate-100 border border-slate-300 rounded-lg hover:bg-slate-200 focus:outline-none focus:ring-2 focus:ring-slate-500">
                        Clear Filters
                    </button>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="px-4 pb-4 pt-3 overflow-x-auto">
            <table class="w-full text-sm text-left text-slate-700">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3">
                            <input type="checkbox" wire:model.live="selectAll"
                                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th class="px-4 py-3 font-medium">
                            <button wire:click="sortBy('po_number')" class="flex items-center gap-1 hover:text-slate-900">
                                PO Number
                                @if($sortBy === 'po_number')
                                    <span class="text-xs {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}">▲</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 font-medium hidden sm:table-cell">
                            <button wire:click="sortBy('invoice_date')" class="flex items-center gap-1 hover:text-slate-900">
                                Invoice Date
                                @if($sortBy === 'invoice_date')
                                    <span class="text-xs {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}">▲</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 font-medium hidden md:table-cell">Invoice Number</th>
                        <th class="px-4 py-3 font-medium">Vendor</th>
                        <th class="px-4 py-3 font-medium hidden lg:table-cell">Creator</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium hidden sm:table-cell">
                            <button wire:click="sortBy('total')" class="flex items-center gap-1 hover:text-slate-900">
                                Total
                                @if($sortBy === 'total')
                                    <span class="text-xs {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}">▲</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 font-medium hidden xl:table-cell">
                            <button wire:click="sortBy('approved_date')" class="flex items-center gap-1 hover:text-slate-900">
                                Approved At
                                @if($sortBy === 'approved_date')
                                    <span class="text-xs {{ $sortDirection === 'asc' ? 'rotate-180' : '' }}">▲</span>
                                @endif
                            </button>
                        </th>
                        <th class="px-4 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchaseOrders as $po)
                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-3">
                                <input type="checkbox" value="{{ $po->id }}" wire:model.live="selectedIds"
                                        class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="px-4 py-3 font-medium text-slate-900">
                                <div class="flex flex-col">
                                    <span>{{ $po->po_number }}</span>
                                    <div class="sm:hidden text-xs text-slate-500 mt-1 space-y-1">
                                        @if($po->invoice_date)
                                            <div>{{ $po->invoice_date->format('d/m/Y') }}</div>
                                        @endif
                                        @if($po->total)
                                            <div class="font-mono">{{ number_format($po->total, 0, ',', '.') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 hidden sm:table-cell">
                                {{ $po->invoice_date ? $po->invoice_date->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-slate-600 hidden md:table-cell">{{ $po->invoice_number ?: '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col">
                                    <span>{{ $po->vendor_name }}</span>
                                    <span class="lg:hidden text-xs text-slate-500">{{ $po->user?->name ?: 'Unknown' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 hidden lg:table-cell">{{ $po->user?->name ?: 'Unknown' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $po->getStatusEnum()->cssClass() }}"
                                      title="{{ $po->getStatusEnum()->description() }} - Created {{ $po->created_at->diffForHumans() }}">
                                    {{ $po->getStatusEnum()->label() }}
                                    @if($po->getStatusEnum()->isPendingApproval())
                                        <span class="ml-1 w-2 h-2 bg-orange-400 rounded-full animate-pulse" title="Awaiting approval"></span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono hidden sm:table-cell">{{ number_format($po->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-slate-600 hidden xl:table-cell">
                                {{ $po->approved_date ? $po->approved_date->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <button wire:click="$dispatch('openDetailModal', {{ $po->id }})"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                            title="View detailed purchase order information">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        View
                                    </button>
                                    @if($po->getStatusEnum()->canEdit())
                                        <a href="{{ route('po.edit', $po->id) }}"
                                           class="text-slate-600 hover:text-slate-900 text-sm font-medium"
                                           title="Edit purchase order details">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                            Edit
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                      @empty
                          <tr>
                              <td colspan="10" class="px-4 py-12 text-center text-slate-500">
                                  <div class="flex flex-col items-center">
                                      <svg class="h-12 w-12 text-slate-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                      </svg>
                                      <p>No purchase orders found matching your criteria.</p>
                                      <p class="text-xs mt-1">Try adjusting your filters or create a new purchase order.</p>
                                  </div>
                              </td>
                          </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            @if ($purchaseOrders->hasPages())
                <div class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-slate-700">
                        Showing {{ $purchaseOrders->firstItem() }} to {{ $purchaseOrders->lastItem() }}
                        of {{ $purchaseOrders->total() }} results
                    </div>
                    <div class="flex gap-1">
                        @if ($purchaseOrders->onFirstPage())
                            <span class="px-3 py-1 text-sm border border-slate-200 rounded bg-slate-50 text-slate-400 cursor-not-allowed">Previous</span>
                        @else
                            <a href="{{ $purchaseOrders->previousPageUrl() }}" wire:click.prevent="setPage({{ $purchaseOrders->currentPage() - 1 }})"
                               class="px-3 py-1 text-sm border border-slate-200 rounded hover:bg-slate-50">Previous</a>
                        @endif

                        @foreach ($purchaseOrders->getUrlRange(1, $purchaseOrders->lastPage()) as $page => $url)
                            <a href="{{ $url }}" wire:click.prevent="setPage({{ $page }})"
                               class="px-3 py-1 text-sm border rounded {{ $page == $purchaseOrders->currentPage() ? 'bg-indigo-600 text-white border-indigo-600' : 'border-slate-200 hover:bg-slate-50' }}">
                                {{ $page }}
                            </a>
                        @endforeach

                        @if ($purchaseOrders->hasMorePages())
                            <a href="{{ $purchaseOrders->nextPageUrl() }}" wire:click.prevent="setPage({{ $purchaseOrders->currentPage() + 1 }})"
                               class="px-3 py-1 text-sm border border-slate-200 rounded hover:bg-slate-50">Next</a>
                        @else
                            <span class="px-3 py-1 text-sm border border-slate-200 rounded bg-slate-50 text-slate-400 cursor-not-allowed">Next</span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- PO Detail Modal --}}
    <livewire:purchase-order.purchase-order-detail />

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