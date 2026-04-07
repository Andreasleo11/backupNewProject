<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 space-y-8" x-data="{ search: @entangle('search') }">
    
    {{-- Header Section --}}
    <div class="glass-card shadow-xl overflow-hidden relative pt-10 pb-8 px-8 border-none">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/10 via-purple-600/5 to-transparent pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-5">
                <div class="h-16 w-16 rounded-3xl bg-gradient-to-br from-indigo-600 to-violet-700 flex items-center justify-center text-white shadow-2xl shadow-indigo-200">
                    <i class="bi bi-shield-check text-3xl"></i>
                </div>
                <div>
                    <h1 class="text-3xl font-black text-slate-900 tracking-tight">Unified Approval Inbox</h1>
                    <p class="text-slate-500 font-medium mt-1">Review and action all pending workflow requests across the system.</p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-2xl flex items-center gap-3 shadow-sm">
                    <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Total Pending</span>
                    <span class="text-2xl font-black text-indigo-700">{{ $approvals->total() }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="glass-card p-4 flex flex-col sm:flex-row items-center gap-4 bg-white/60 backdrop-blur-md shadow-sm border border-slate-100 rounded-3xl">
        <div class="relative w-full sm:w-80 group">
            <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors"></i>
            <input type="text" 
                   wire:model.debounce.500ms="search" 
                   placeholder="Search ID, Ref, or Type..." 
                   class="w-full pl-11 pr-4 py-3 rounded-2xl border-slate-100 bg-slate-50/50 text-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all">
        </div>

        <div class="h-8 w-px bg-slate-200 mx-2 hidden sm:block"></div>

        <div class="flex items-center gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-hide">
            <button wire:click="$set('filterType', '')" 
                    class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all {{ $filterType === '' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-100' }}">
                All Requests
            </button>
            <button wire:click="$set('filterType', 'Purchase')" 
                    class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all {{ $filterType === 'Purchase' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-100' }}">
                Purchase Requests
            </button>
            <button wire:click="$set('filterType', 'Overtime')" 
                    class="px-5 py-2.5 rounded-xl text-sm font-bold transition-all {{ $filterType === 'Overtime' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-100' : 'bg-white text-slate-500 hover:bg-slate-50 border border-slate-100' }}">
                Overtime
            </button>
        </div>
    </div>

    {{-- Results Table --}}
    <div class="glass-card shadow-2xl border-none overflow-hidden rounded-[2.5rem]">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        <th class="px-8 py-5 text-[11px] font-black uppercase tracking-widest text-slate-400">Request Details</th>
                        <th class="px-8 py-5 text-[11px] font-black uppercase tracking-widest text-slate-400">Department / Branch</th>
                        <th class="px-8 py-5 text-[11px] font-black uppercase tracking-widest text-slate-400">Submitted At</th>
                        <th class="px-8 py-5 text-[11px] font-black uppercase tracking-widest text-slate-400">Workflow Status</th>
                        <th class="px-8 py-5 text-[11px] font-black uppercase tracking-widest text-slate-400 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($approvals as $approvalStep)
                        @php
                            $request = $approvalStep->request;
                            $approvable = $request->approvable;
                            if (!$approvable) continue;
                        @endphp
                        <tr class="group hover:bg-indigo-50/30 transition-colors">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-indigo-100 group-hover:text-indigo-600 transition-colors shadow-inner">
                                        <i class="bi bi-file-earmark-text text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-slate-800">{{ $approvable->getApprovableTypeLabel() }}</p>
                                        <p class="text-xs text-slate-400 font-bold mt-0.5 tracking-tight">Ref: {{ $approvable->getApprovableIdentifier() }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="space-y-1">
                                    <p class="text-sm font-bold text-slate-700">{{ $approvable->getApprovableDepartmentName() ?? 'General' }}</p>
                                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $approvable->getApprovableBranchValue() ?? 'HO' }}</p>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-2 text-slate-500 text-sm font-medium">
                                    <i class="bi bi-clock text-indigo-400"></i>
                                    {{ $request->submitted_at ? \Carbon\Carbon::parse($request->submitted_at)->format('d M Y, H:i') : '-' }}
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-col items-start gap-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest bg-amber-50 text-amber-700 border border-amber-200 shadow-sm">
                                        Level {{ $approvalStep->sequence }} of {{ $request->steps_count ?? '?' }}
                                    </span>
                                    <p class="text-[10px] text-slate-500 font-bold italic tracking-wide group-hover:text-slate-700 transition-colors">
                                        {{ $approvalStep->role_name ?? 'Approver' }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    {{-- Quick View Button --}}
                                    @php
                                        $quickViewUrl = match(get_class($approvable)) {
                                            'App\Models\PurchaseRequest' => route('purchase-requests.quick-view', $approvable->id),
                                            'App\Domain\Overtime\Models\OvertimeForm' => route('overtime.detail', $approvable->id),
                                            default => null
                                        };
                                    @endphp
                                    
                                    @if($quickViewUrl)
                                        <button wire:click="openQuickView({{ $approvable->id }}, '{{ addslashes(get_class($approvable)) }}')"
                                                class="h-10 w-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 border border-slate-100 hover:bg-white hover:text-indigo-600 hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-100 transition-all active:scale-95 group/view"
                                                title="Quick Preview">
                                            <i class="bi bi-eye-fill text-lg group-hover/view:scale-110 transition-transform"></i>
                                        </button>
                                    @endif

                                    <a href="{{ $approvable->getApprovableShowUrl() }}" 
                                       class="inline-flex items-center gap-2 bg-white border border-slate-200 px-5 py-2.5 rounded-xl text-sm font-bold text-slate-700 shadow-sm transition-all hover:bg-indigo-600 hover:text-white hover:border-indigo-600 hover:shadow-xl hover:shadow-indigo-100 hover:-translate-y-1 group/btn active:scale-95">
                                        <span>Open Review</span>
                                        <i class="bi bi-arrow-right group-hover/btn:translate-x-1 transition-transform"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-20 text-center">
                                <div class="flex flex-col items-center justify-center max-w-xs mx-auto">
                                    <div class="h-20 w-20 rounded-full bg-slate-50 flex items-center justify-center text-slate-200 mb-6">
                                        <i class="bi bi-check-all text-5xl"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-slate-800">Inbox Clean!</h4>
                                    <p class="text-slate-400 text-sm mt-2">You don't have any pending approvals at the moment. Great job!</p>
                                    @if($search || $filterType)
                                        <button wire:click="resetFilters" class="mt-6 text-indigo-600 font-bold text-sm hover:underline">Clear all filters</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($approvals->hasPages())
            <div class="bg-white border-t border-slate-50 px-8 py-6">
                {{ $approvals->links() }}
            </div>
        @endif
    </div>

    {{-- QUICK VIEW MODAL --}}
    <div x-data="{ open: false }" 
         @open-quick-view-modal.window="open = true"
         @close-quick-view-modal.window="open = false"
         x-show="open"
         class="fixed inset-0 z-[100] overflow-y-auto"
         style="display: none;">
        
        <div class="flex min-h-screen items-center justify-center p-4">
            {{-- Backdrop --}}
            <div x-show="open" 
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="open = false; $wire.closeQuickView()"
                 class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>

            {{-- Modal Content --}}
            <div x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative w-full max-w-4xl transform rounded-[2rem] bg-white shadow-2xl transition-all border border-white/20">
                
                <div class="flex items-center justify-between border-b border-slate-100 px-8 py-6">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-inner">
                            <i class="bi bi-eye text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-black text-slate-900">Quick Preview</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-0.5">Reference ID: {{ $selectedId }}</p>
                        </div>
                    </div>
                    <button @click="open = false; $wire.closeQuickView()" class="h-10 w-10 flex items-center justify-center rounded-xl hover:bg-slate-50 text-slate-400 transition-colors">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="p-4 bg-slate-50 min-h-[500px] max-h-[70vh] overflow-y-auto custom-scrollbar-thin">
                    @if($selectedId && $selectedType)
                        @if($selectedType === 'App\Models\PurchaseRequest')
                            @livewire('purchase-request.quick-view', ['prId' => $selectedId], key('pr-'.$selectedId))
                        @elseif($selectedType === 'App\Domain\Overtime\Models\OvertimeForm')
                            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                                @livewire('overtime.detail', ['id' => $selectedId], key('ot-'.$selectedId))
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-full py-20 text-center">
                                <div class="h-16 w-16 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center mb-4">
                                    <i class="bi bi-exclamation-triangle text-3xl"></i>
                                </div>
                                <h4 class="text-lg font-bold text-slate-800">No Preview Available</h4>
                                <p class="text-slate-400 max-w-xs mx-auto mt-2 text-sm italic">Detailed preview for this module is still in development. Please use the full review page.</p>
                            </div>
                        @endif
                    @else
                        <div class="flex items-center justify-center h-full py-32">
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-indigo-600 border-t-transparent"></div>
                        </div>
                    @endif
                </div>

                <div class="px-8 py-6 bg-white rounded-b-[2rem] flex items-center justify-between border-t border-slate-100">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest italic">Best practice: Always verify line items before signing off.</p>
                    <div class="flex items-center gap-3">
                        <button @click="open = false; $wire.closeQuickView()" class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-50 transition-all">Close</button>
                        <a href="{{ $selectedId ? '#' : '' }}" class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all">Open Full Detail</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
