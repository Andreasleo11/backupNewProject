<div x-data="{ 
    showFilters: false,
    selectedIds: @entangle('selectedIds').live
}" class="space-y-4">
    
    {{-- Background Polling Status --}}
    @if(!empty($processingIds))
        <div wire:poll.3s="checkProcessingStatus" class="hidden"></div>
    @endif

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-xl font-black text-slate-900 tracking-tight uppercase">Purchase Orders</h1>
            <nav class="flex items-center gap-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                <a href="{{ route('po.dashboard') }}" class="hover:text-indigo-600 transition-colors">Dashboard</a>
                <i class="bi bi-chevron-right text-[8px]"></i>
                <span class="text-slate-600">List</span>
            </nav>
        </div>
        
        <div class="flex items-center gap-2">
            <div wire:loading.delay wire:target="search, statusFilter, vendorFilter, dateFrom, dateTo, amountFrom, amountTo" class="flex items-center gap-2 px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-xl animate-pulse">
                <div class="h-1.5 w-1.5 rounded-full bg-indigo-600 animate-bounce"></div>
                <span class="text-[9px] font-black uppercase tracking-widest">Updating...</span>
            </div>
        </div>
    </div>

    {{-- Compact Stats Bar --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        {{-- Pending Me --}}
        <button wire:click="filterByStat('pending_me')" 
                class="group bg-white rounded-2xl p-3.5 flex items-center gap-3.5 border border-slate-100 shadow-sm hover:shadow-md transition-all text-left relative overflow-hidden">
            <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-inner group-hover:bg-indigo-600 group-hover:text-white transition-all">
                <i class="bi bi-person-check-fill"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">My Action</p>
                <p class="text-lg font-black text-slate-900 leading-none">{{ $this->stats['pending_me'] }}</p>
            </div>
        </button>

        {{-- Active Reviews --}}
        <button wire:click="filterByStat('in_review')"
                class="group bg-white rounded-2xl p-3.5 flex items-center gap-3.5 border border-slate-100 shadow-sm hover:shadow-md transition-all text-left relative overflow-hidden">
            <div class="h-10 w-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-inner group-hover:bg-amber-600 group-hover:text-white transition-all">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">In Review</p>
                <p class="text-lg font-black text-slate-900 leading-none">{{ $this->stats['in_review'] }}</p>
            </div>
        </button>

        {{-- Monthly Rejections --}}
        <button wire:click="filterByStat('rejected')"
                class="group bg-white rounded-2xl p-3.5 flex items-center gap-3.5 border border-slate-100 shadow-sm hover:shadow-md transition-all text-left relative overflow-hidden">
            <div class="h-10 w-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl shadow-inner group-hover:bg-rose-600 group-hover:text-white transition-all">
                <i class="bi bi-x-octagon-fill"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider mb-0.5">Rejected</p>
                <p class="text-lg font-black text-slate-900 leading-none">{{ $this->stats['rejected_month'] }}</p>
            </div>
        </button>

        {{-- Monthly Valuation --}}
        <div class="group bg-indigo-600 rounded-2xl p-3.5 flex items-center gap-3.5 shadow-lg shadow-indigo-100 transition-all text-left relative overflow-hidden text-white">
            <div class="h-10 w-10 rounded-xl bg-white/20 text-white flex items-center justify-center text-xl shadow-inner">
                <i class="bi bi-currency-dollar"></i>
            </div>
            <div>
                <p class="text-[9px] font-black text-indigo-100 uppercase tracking-wider mb-0.5">Valuation (MTD)</p>
                <p class="text-lg font-black leading-none">{{ number_format($this->stats['total_valuation'], 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Main Toolbar - Enterprise Compact --}}
    <div class="bg-white/90 backdrop-blur-xl border border-slate-200/60 rounded-2xl p-3 shadow-sm space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            {{-- Search & Direct Filters --}}
            <div class="flex flex-1 items-center gap-2 min-w-0">
                <div class="relative flex-1 max-w-sm group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="bi bi-search text-slate-400 text-xs transition-colors group-focus-within:text-indigo-500"></i>
                    </div>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search"
                           placeholder="Search PO, Vendor, Invoice..."
                           class="w-full pl-9 pr-3 py-2 bg-slate-50 border-transparent rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-500/30 transition-all">
                </div>
                
                <div class="flex items-center gap-1.5 whitespace-nowrap">
                    <select wire:model.live="statusFilter"
                            class="bg-slate-50 border-transparent rounded-xl text-[10px] font-black uppercase tracking-wider text-slate-600 focus:ring-2 focus:ring-indigo-500/10 py-2 px-3 transition-all">
                        @foreach($filters['statuses'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select wire:model.live="perPage"
                            class="bg-slate-50 border-transparent rounded-xl text-[10px] font-black uppercase tracking-wider text-slate-600 focus:ring-2 focus:ring-indigo-500/10 py-2 px-3 transition-all">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}">{{ $option }} Rows</option>
                        @endforeach
                    </select>

                    <button wire:click="clearFilters" 
                            class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-xl transition-all"
                            title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise text-sm"></i>
                    </button>

                    <button @click="showFilters = !showFilters"
                            :class="showFilters ? 'bg-indigo-600 text-white shadow-md shadow-indigo-100' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                            class="flex items-center gap-1.5 py-2 px-3 rounded-xl text-[10px] font-black uppercase tracking-wider transition-all">
                        <i class="bi bi-sliders text-xs"></i>
                        Filters
                    </button>
                </div>
            </div>

            {{-- Create Button --}}
            @if (auth()->user()->department?->name !== 'MANAGEMENT' || auth()->user()->hasRole('super-admin'))
                <a href="{{ route('po.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-md hover:bg-indigo-600 transition-all">
                    <i class="bi bi-plus-lg"></i>
                    New PO
                </a>
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
                <div class="space-y-1.5">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-wider ml-1">Vendor Entity</label>
                    <select wire:model.live="vendorFilter"
                            class="w-full bg-slate-50 border-transparent rounded-xl text-[10px] font-bold text-slate-600 py-2 px-3">
                        @foreach($filters['vendors'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Date Range --}}
                <div class="space-y-1.5">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-wider ml-1">Invoice Period</label>
                    <div class="flex items-center gap-2">
                        <input type="date" wire:model.live="dateFrom" class="flex-1 bg-slate-50 border-transparent rounded-xl text-[10px] font-bold text-slate-600 py-2 px-3">
                        <span class="text-slate-300">-</span>
                        <input type="date" wire:model.live="dateTo" class="flex-1 bg-slate-50 border-transparent rounded-xl text-[10px] font-bold text-slate-600 py-2 px-3">
                    </div>
                </div>

                {{-- Amount Range --}}
                <div class="space-y-1.5">
                    <label class="text-[9px] font-black text-slate-400 uppercase tracking-wider ml-1">Total Valuation (IDR)</label>
                    <div class="flex items-center gap-2">
                        <input type="number" wire:model.live="amountFrom" placeholder="Min" class="flex-1 bg-slate-50 border-transparent rounded-xl text-[10px] font-bold text-slate-600 py-2 px-3">
                        <input type="number" wire:model.live="amountTo" placeholder="Max" class="flex-1 bg-slate-50 border-transparent rounded-xl text-[10px] font-bold text-slate-600 py-2 px-3">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Section - Compact --}}
    <div class="bg-white border border-slate-200/60 rounded-2xl overflow-hidden shadow-sm relative z-0">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/50">
                        <th class="pl-5 pr-3 py-3 w-10">
                            <input type="checkbox" wire:model.live="selectAll" class="h-4 w-4 rounded border-slate-200 text-indigo-600 focus:ring-indigo-500/10 transition-all">
                        </th>
                        <th class="px-3 py-3">
                            <button wire:click="sortBy('po_number')" class="group flex items-center gap-1.5 text-[9px] font-black text-slate-400 uppercase tracking-wider hover:text-indigo-600 transition-colors">
                                PO Number
                                <i class="bi bi-arrow-down-up {{ $sortBy === 'po_number' ? 'text-indigo-500' : 'opacity-0' }}"></i>
                            </button>
                        </th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-wider">Vendor</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-wider">Creator</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-wider hidden md:table-cell">Status</th>
                        <th class="px-3 py-3 text-[9px] font-black text-slate-400 uppercase tracking-wider hidden lg:table-cell">Valuation</th>
                        <th class="px-6 py-3 text-right text-[9px] font-black text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($purchaseOrders as $po)
                        @php $isProcessing = in_array($po->id, $processingIds); @endphp
                        <tr class="group hover:bg-slate-50/50 transition-all {{ $isProcessing ? 'opacity-40 grayscale pointer-events-none' : '' }}">
                            <td class="pl-5 pr-3 py-2.5 text-center">
                                <input type="checkbox" value="{{ $po->id }}" wire:model.live="selectedIds" class="h-4 w-4 rounded border-slate-200 text-indigo-600 focus:ring-indigo-500/10 transition-all">
                            </td>
                            <td class="px-3 py-2.5">
                                <div class="flex flex-col">
                                    <span class="text-xs font-black text-slate-900 group-hover:text-indigo-600 transition-colors">{{ $po->po_number }}</span>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        @if($po->workflow_status === 'IN_REVIEW')
                                            @php $daysPending = now()->diffInDays($po->approvalRequest?->submitted_at ?? $po->created_at); @endphp
                                            <span class="text-[8px] font-black px-1.5 py-0.5 rounded border {{ $daysPending > 3 ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-slate-50 text-slate-400 border-slate-100' }}">
                                                {{ $daysPending }}D
                                            </span>
                                        @endif
                                        <span class="text-[9px] font-bold text-slate-400">{{ $po->invoice_date?->format('d M Y') ?? '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-2.5">
                                <div class="flex flex-col">
                                    <span class="text-xs font-bold text-slate-800 truncate max-w-[150px]">{{ $po->vendor_name }}</span>
                                    <span class="text-[9px] font-medium text-slate-400 italic truncate max-w-[120px]">{{ $po->invoice_number ?: 'No Invoice' }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-6 rounded-lg bg-slate-100 flex items-center justify-center text-[9px] font-black text-slate-500 shadow-inner">
                                        {{ mb_substr($po->user?->name ?? '?', 0, 1) }}
                                    </div>
                                    <div class="flex flex-col leading-tight">
                                        <span class="text-[10px] font-bold text-slate-700">{{ $po->user?->name ?: 'System' }}</span>
                                        <span class="text-[8px] font-medium text-slate-400 uppercase tracking-tighter">{{ $po->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-2.5 hidden md:table-cell">
                                <div class="flex flex-col gap-1.5">
                                    <span class="inline-flex items-center w-fit px-2 py-0.5 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $po->getStatusEnum()->cssClass() }}">
                                        {{ $isProcessing ? 'Processing' : $po->getStatusEnum()->label() }}
                                    </span>
                                    @if($po->workflow_status === 'IN_REVIEW')
                                        <div class="w-16 h-0.5 bg-slate-100 rounded-full overflow-hidden">
                                            @php
                                                $totalSteps = $po->approvalRequest?->steps->count() ?: 1;
                                                $currentStep = $po->approvalRequest?->current_step ?: 1;
                                                $percentage = ($currentStep / $totalSteps) * 100;
                                            @endphp
                                            <div class="h-full bg-indigo-500 transition-all duration-500" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-3 py-2.5 hidden lg:table-cell font-mono text-xs font-black text-slate-900">
                                {{ number_format($po->total, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button wire:click="openDetailModal({{ $po->id }})" class="p-1.5 bg-slate-50 text-slate-400 rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-all" title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('po.view', $po->id) }}" class="p-1.5 bg-slate-50 text-slate-400 rounded-lg hover:bg-slate-900 hover:text-white transition-all" title="Open">
                                        <i class="bi bi-box-arrow-up-right"></i>
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
        
        @if ($purchaseOrders->hasPages())
            <div class="px-6 py-3 bg-slate-50/50 border-t border-slate-100">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
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
                    <button @click="$wire.selectedIds = []" 
                            class="p-2 text-white/40 hover:text-white rounded-xl transition-all">
                        <i class="bi bi-trash3 text-xs"></i>
                    </button>
                </div>
            </div>
        </template>
    @endrole

    {{-- Detail Modal - Compact --}}
    <template x-teleport="body">
        <div x-data="{ open: @entangle('showDetailModal') }" 
             x-show="open" 
             x-cloak
             class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            
            <div x-show="open" x-transition.opacity class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm" @click="open = false"></div>

            <div x-show="open" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 scale-95 translate-y-4" 
                 x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                 class="relative w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col border border-slate-200/50">
                
                @if($modalLoading)
                    <div class="absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center z-50">
                        <div class="h-10 w-10 border-4 border-indigo-100 border-t-indigo-600 rounded-xl animate-spin"></div>
                    </div>
                @endif

                @if($selectedPurchaseOrder)
                    <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-6">
                        <div class="flex items-center justify-between">
                            <div class="space-y-1">
                                <p class="text-[9px] font-black text-indigo-600 uppercase tracking-widest">Purchase Order Details</p>
                                <h2 class="text-2xl font-black text-slate-900 tracking-tight">{{ $selectedPurchaseOrder->po_number }}</h2>
                            </div>
                            <button @click="open = false" class="h-8 w-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-rose-50 hover:text-rose-600 transition-all">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total</p>
                                <p class="text-lg font-black text-slate-900">{{ number_format($selectedPurchaseOrder->total, 0, ',', '.') }} <span class="text-[10px] text-slate-400 ml-0.5">IDR</span></p>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Vendor</p>
                                <p class="text-sm font-bold text-slate-800 truncate">{{ $selectedPurchaseOrder->vendor_name }}</p>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Invoice</p>
                                <p class="text-sm font-bold text-slate-800">{{ $selectedPurchaseOrder->invoice_number ?: '-' }}</p>
                            </div>
                            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100 text-center">
                                <span class="inline-flex px-3 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider {{ $selectedPurchaseOrder->getStatusEnum()->cssClass() }}">
                                    {{ $selectedPurchaseOrder->getStatusEnum()->label() }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="lg:col-span-2 space-y-6">
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Requester</label>
                                        <p class="text-xs font-bold text-slate-700">{{ $selectedPurchaseOrder->user?->name }}</p>
                                    </div>
                                    <div>
                                        <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Created At</label>
                                        <p class="text-xs font-bold text-slate-700">{{ $selectedPurchaseOrder->created_at->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <h4 class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Approval Workflow</h4>
                                    <div class="space-y-3">
                                        @foreach($selectedPurchaseOrder->approvalRequest?->steps ?? [] as $step)
                                            <div class="flex items-center gap-3">
                                                <div class="h-7 w-7 rounded-lg flex items-center justify-center text-[10px] font-black
                                                    {{ $step->status === 'APPROVED' ? 'bg-indigo-600 text-white' : ($step->sequence == $selectedPurchaseOrder->approvalRequest->current_step ? 'bg-amber-100 text-amber-600 animate-pulse' : 'bg-slate-50 text-slate-300') }}">
                                                    {{ $step->sequence }}
                                                </div>
                                                <div class="flex-1 flex items-center justify-between min-w-0">
                                                    <span class="text-[10px] font-bold text-slate-600 truncate">{{ $step->approver_snapshot_label ?: $step->approver_label }}</span>
                                                    @if($step->remarks)
                                                        <span class="text-[9px] text-slate-400 italic truncate ml-4">"{{ Str::limit($step->remarks, 30) }}"</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="p-5 bg-indigo-600 rounded-2xl text-white shadow-xl shadow-indigo-100 relative overflow-hidden group">
                                    <div class="relative z-10 space-y-4">
                                        <div class="h-10 w-10 bg-white/20 rounded-xl flex items-center justify-center text-xl shadow-inner">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </div>
                                        <button wire:click="downloadPdf" class="w-full py-3 bg-white text-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-50 transition-all shadow-lg shadow-indigo-900/10">
                                            Download PDF
                                        </button>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    @if($this->canApproveSelectedPO())
                                        <button wire:click="approvePurchaseOrder" class="w-full py-3 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-md">
                                            Approve & Sign
                                        </button>
                                    @endif
                                    @if($this->canRejectSelectedPO())
                                        <button @click="$dispatch('open-reject-modal')" class="w-full py-3 bg-white text-rose-600 border border-rose-200 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-rose-50 transition-all">
                                            Reject Request
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
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
</div>
