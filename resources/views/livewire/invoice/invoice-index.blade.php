<div>
    <div class="px-4 sm:px-6 lg:px-8 py-6 max-w-[1600px] mx-auto space-y-6">
        {{-- Header Section --}}
        <div class="sm:flex sm:items-center sm:justify-between bg-white p-6 rounded-3xl border border-slate-100 shadow-sm relative overflow-hidden group">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-emerald-50 rounded-full blur-3xl opacity-50 group-hover:bg-emerald-100 transition-colors duration-500"></div>
            
            <div class="relative">
                <div class="flex items-center gap-4 flex-wrap">
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
                        <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-inner">
                            <i class="bi bi-receipt"></i>
                        </div>
                        Invoices
                    </h1>
                </div>
                <p class="mt-2 text-sm text-slate-500 font-medium max-w-2xl">
                    Manage and track all invoices connected to purchase orders across the organization.
                </p>
            </div>
            
            <div class="mt-4 sm:mt-0 relative z-10 flex gap-3">
                <a href="{{ route('po.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-slate-50 transition-all hover:ring-slate-300">
                    <i class="bi bi-arrow-left"></i>
                    Back to POs
                </a>
            </div>
        </div>

        {{-- Filters Bar --}}
        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="flex-1 w-full relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="bi bi-search text-slate-400"></i>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text" 
                       class="block w-full rounded-xl border-0 py-3 pl-11 pr-4 text-slate-900 ring-1 ring-inset ring-slate-200 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm sm:leading-6 font-medium transition-all" 
                       placeholder="Search by Invoice #, PO #, or Vendor...">
            </div>
            
            <div class="flex items-center gap-3 w-full md:w-auto">
                <select wire:model.live="perPage" class="rounded-xl border-0 py-3 pl-4 pr-10 text-slate-900 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-emerald-600 sm:text-sm font-bold bg-slate-50">
                    <option value="10">10 per page</option>
                    <option value="25">25 per page</option>
                    <option value="50">50 per page</option>
                </select>

                <button wire:click="clearFilters" class="rounded-xl bg-slate-50 px-4 py-3 text-sm font-bold text-slate-600 shadow-sm ring-1 ring-inset ring-slate-200 hover:bg-slate-100 hover:text-slate-900 transition-all whitespace-nowrap">
                    Clear Filters
                </button>
            </div>
        </div>

        {{-- Data Table --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden relative">
            <div class="overflow-x-auto min-h-[400px]">
                <table class="min-w-full divide-y divide-slate-100 table-fixed">
                    <thead>
                        <tr class="bg-slate-50/80">
                            <th scope="col" class="relative px-6 py-4 w-12">
                                <span class="sr-only">Row ID</span>
                            </th>
                            <th scope="col" class="w-[20%] px-3 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-widest cursor-pointer hover:text-emerald-600 transition-colors group" wire:click="sortByColumn('invoice_number')">
                                <div class="flex items-center gap-2">
                                    Invoice
                                    @if ($sortBy === 'invoice_number')
                                        <i class="bi bi-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-emerald-500"></i>
                                    @else
                                        <i class="bi bi-sort-up opacity-0 group-hover:opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="w-[25%] px-3 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-widest">
                                Parent PO / Vendor
                            </th>
                            <th scope="col" class="w-[15%] px-3 py-4 text-left text-xs font-black text-slate-400 uppercase tracking-widest cursor-pointer hover:text-emerald-600 transition-colors group" wire:click="sortByColumn('invoice_date')">
                                <div class="flex items-center gap-2">
                                    Dates
                                    @if ($sortBy === 'invoice_date')
                                        <i class="bi bi-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-emerald-500"></i>
                                    @else
                                        <i class="bi bi-sort-up opacity-0 group-hover:opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="w-[15%] px-3 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-widest cursor-pointer hover:text-emerald-600 transition-colors group" wire:click="sortByColumn('total')">
                                <div class="flex items-center justify-end gap-2">
                                    Total Amount
                                    @if ($sortBy === 'total')
                                        <i class="bi bi-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-emerald-500"></i>
                                    @else
                                        <i class="bi bi-sort-up opacity-0 group-hover:opacity-50"></i>
                                    @endif
                                </div>
                            </th>
                            <th scope="col" class="w-[15%] px-6 py-4 text-right text-xs font-black text-slate-400 uppercase tracking-widest">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 bg-white">
                        @forelse ($invoices as $invoice)
                            <tr class="group hover:bg-slate-50/50 transition-all duration-200">
                                <td class="px-6 py-5 whitespace-nowrap">
                                    <div class="h-8 w-8 rounded-xl bg-slate-50 flex items-center justify-center text-xs font-bold text-slate-400 border border-slate-100">
                                        {{ $loop->iteration + ($invoices->currentPage() - 1) * $invoices->perPage() }}
                                    </div>
                                </td>
                                
                                <td class="px-3 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500 shadow-inner shrink-0">
                                            <i class="bi bi-receipt"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-black text-slate-900 group-hover:text-emerald-600 transition-colors">
                                                {{ $invoice->invoice_number ?? 'No Number' }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-slate-100 text-slate-500">
                                                    {{ $invoice->files->count() }} Attachments
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-3 py-5">
                                    <div class="flex flex-col justify-center">
                                        @if($invoice->purchaseOrder)
                                            <a href="{{ route('po.view', $invoice->purchase_order_id) }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 hover:underline transition-all">
                                                {{ $invoice->purchaseOrder->po_number }}
                                            </a>
                                            <p class="text-xs font-bold text-slate-500 mt-1 truncate max-w-[200px]">
                                                {{ $invoice->purchaseOrder->vendor_name }}
                                            </p>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Orphaned</span>
                                        @endif
                                    </div>
                                </td>
                                
                                <td class="px-3 py-5">
                                    <div class="flex flex-col gap-1.5 justify-center">
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="font-bold text-slate-400 uppercase text-[10px] w-8">Inv:</span>
                                            <span class="font-semibold text-slate-700">{{ $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : '-' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs">
                                            <span class="font-bold text-slate-400 uppercase text-[10px] w-8">Pay:</span>
                                            <span class="font-semibold text-slate-700">{{ $invoice->payment_date ? $invoice->payment_date->format('d M Y') : '-' }}</span>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-3 py-5 text-right whitespace-nowrap">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">{{ $invoice->total_currency }}</span>
                                    <span class="font-mono font-black text-slate-800 text-sm ml-1">{{ number_format($invoice->total, 2, '.', ',') }}</span>
                                </td>
                                
                                <td class="px-6 py-5 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($invoice->purchaseOrder)
                                            <a href="{{ route('po.view', $invoice->purchase_order_id) }}" 
                                               class="h-8 px-3 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white flex items-center gap-2 font-bold transition-all shadow-sm border border-indigo-100">
                                                View PO
                                                <i class="bi bi-arrow-right"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="mx-auto h-24 w-24 bg-slate-50 rounded-full flex items-center justify-center mb-4 ring-8 ring-white shadow-inner">
                                        <i class="bi bi-inbox text-3xl text-slate-300"></i>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">No Invoices Found</h3>
                                    <p class="mt-2 text-xs font-medium text-slate-500 max-w-sm mx-auto leading-relaxed">
                                        We couldn't find any invoices matching your current filters. Try adjusting your search criteria.
                                    </p>
                                    @if($search)
                                        <button wire:click="clearFilters" class="mt-4 px-4 py-2 bg-slate-100 text-slate-600 rounded-xl font-bold text-xs hover:bg-slate-200 transition-colors">
                                            Clear Filters
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($invoices->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $invoices->links(data: ['scrollTo' => false]) }}
                </div>
            @endif
            
            {{-- Loading Overlay --}}
            <div wire:loading.delay.longer wire:target="search, perPage, sortByColumn, clearFilters" 
                 class="absolute inset-0 bg-white/60 backdrop-blur-sm z-10 flex items-center justify-center transition-all">
                <div class="flex flex-col items-center bg-white p-6 rounded-3xl shadow-2xl">
                    <div class="h-10 w-10 border-4 border-emerald-100 border-t-emerald-600 rounded-full animate-spin"></div>
                    <p class="mt-4 text-xs font-black text-slate-600 uppercase tracking-widest">Loading Data...</p>
                </div>
            </div>
        </div>
    </div>
</div>
