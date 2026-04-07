<div class="h-full flex flex-col rounded-3xl bg-white border border-slate-200 shadow-sm overflow-hidden group hover:shadow-xl hover:border-blue-100 transition-all duration-500">
    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/30">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-blue-50 text-blue-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="font-bold text-slate-800 tracking-tight">Pending Approvals</h3>
        </div>
        <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-[10px] font-black uppercase tracking-widest">
            {{ $approvals->count() }} Items
        </span>
    </div>

    <div class="flex-1 overflow-y-auto custom-scrollbar p-6">
        @forelse ($approvals as $step)
            @php
                $request = $step->request;
                $approvable = $request->approvable;
                $type = class_basename($request->approvable_type);
                $bgGradient = match($type) {
                    'PurchaseRequest' => 'from-blue-500/10 to-transparent',
                    'OvertimeForm' => 'from-amber-500/10 to-transparent',
                    default => 'from-slate-500/10 to-transparent'
                };
                $accentColor = match($type) {
                    'PurchaseRequest' => 'text-blue-600 bg-blue-50',
                    'OvertimeForm' => 'text-amber-600 bg-amber-50',
                    default => 'text-slate-600 bg-slate-50'
                };
            @endphp
            
            <div class="relative mb-4 last:mb-0 p-4 rounded-2xl border border-slate-100 bg-white transition-all hover:bg-slate-50/50 hover:scale-[1.01] hover:shadow-md group/item overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r {{ $bgGradient }} opacity-0 group-hover/item:opacity-100 transition-opacity"></div>
                
                <div class="relative z-10 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="flex flex-col">
                            <span class="text-[9px] font-black uppercase tracking-[0.2em] {{ $accentColor }} px-1.5 py-0.5 rounded-md inline-block mb-1 w-fit">
                                {{ preg_replace('/(?<!^)[A-Z]/', ' $0', $type) }}
                            </span>
                            <span class="font-bold text-slate-900 text-sm">
                                @if($approvable)
                                    {{ $approvable->doc_number ?? $approvable->number ?? $approvable->id }}
                                @else
                                    <span class="text-slate-400 italic">Deleted or invalid record</span>
                                @endif
                            </span>
                            <span class="text-[11px] text-slate-500 mt-0.5">
                                Submitted {{ $request->submitted_at?->diffForHumans() ?? 'recently' }} by <span class="font-bold text-slate-700">{{ $request->submittingUser?->name ?? 'User' }}</span>
                            </span>
                        </div>
                    </div>

                    @if($approvable)
                    <div class="flex items-center gap-2">
                        <a href="{{ $approvable->getApprovableShowUrl() }}" 
                           class="flex items-center justify-center h-10 w-10 rounded-xl bg-white border border-slate-200 text-slate-400 hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50 transition-all shadow-sm"
                           title="Open Full Detail">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                        <button wire:click="openQuickView({{ $approvable->id }}, '{{ addslashes(get_class($approvable)) }}')" 
                                class="h-10 px-4 rounded-xl bg-blue-600 text-white text-[11px] font-black uppercase tracking-widest shadow-lg shadow-blue-200 hover:bg-blue-700 hover:scale-105 transition-all">
                            Review
                        </button>
                    </div>
                    @endif

                </div>
            </div>
        @empty
            <div class="h-full flex flex-col items-center justify-center py-10 opacity-40 grayscale">
                <div class="h-20 w-20 rounded-full bg-slate-100 flex items-center justify-center mb-4">
                    <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-slate-500 font-bold uppercase tracking-widest text-xs">All caught up!</p>
            </div>
        @endforelse
    </div>
    
    <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100 mt-auto">
        <a href="{{ route('approvals') }}" class="text-[11px] font-black uppercase tracking-widest text-blue-600 hover:text-blue-800 transition-colors flex items-center gap-2 group/all">
            View All Requests
            <svg class="h-3 w-3 group-hover/all:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </a>
    </div>

    {{-- QUICK VIEW MODAL --}}
    @teleport('body')
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
                        <div class="h-12 w-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center shadow-inner">
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
                            <div class="animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent"></div>
                        </div>
                    @endif
                </div>

                <div class="px-8 py-6 bg-white rounded-b-[2rem] flex items-center justify-between border-t border-slate-100">
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest italic">Best practice: Always verify line items before signing off.</p>
                    <div class="flex items-center gap-3">
                        <button @click="open = false; $wire.closeQuickView()" class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-50 transition-all">Close</button>
                        <a href="{{ $selectedId ? '#' : '' }}" class="px-6 py-2.5 rounded-xl bg-blue-600 text-white text-sm font-bold shadow-lg shadow-blue-100 hover:bg-blue-700 transition-all">Open Full Detail</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endteleport
</div>
