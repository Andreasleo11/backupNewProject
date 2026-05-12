{{-- ===== FLOATING BULK ACTION BAR =====
     Appears (teleported to body) when selectedIds.length > 0.
     Unchanged from original design.
--}}
@if ($canApprove)
    <template x-teleport="body">
        <div x-cloak x-show="selectedIds.length > 0"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-10"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-10"
            class="fixed bottom-8 left-1/2 -translate-x-1/2 z-50">

            <div class="bg-slate-900 border border-slate-700/50 rounded-2xl px-5 py-3.5 shadow-2xl flex items-center gap-5 min-w-[360px]">
                {{-- Count --}}
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black text-sm shrink-0">
                        <span x-text="selectedIds.length"></span>
                    </div>
                    <div>
                        <p class="text-xs font-black text-white uppercase tracking-tight leading-none">Selected</p>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Ready for batch action</p>
                    </div>
                </div>

                <div class="h-7 w-px bg-slate-700 mx-1 shrink-0"></div>

                <div class="flex items-center gap-2.5">
                    <button type="button" wire:click="loadSnapshot" wire:loading.attr="disabled"
                        class="h-9 px-5 rounded-xl bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest hover:bg-indigo-500 transition-all shadow-lg shadow-indigo-500/20">
                        <span wire:loading.remove wire:target="loadSnapshot">Review & Sign Selected</span>
                        <span wire:loading wire:target="loadSnapshot" class="flex items-center gap-1.5">
                            <i class='bx bx-loader-alt animate-spin'></i> Analyzing…
                        </span>
                    </button>
                    <button type="button" @click="selectedIds = []"
                        class="text-[10px] font-black text-slate-400 hover:text-white transition-colors uppercase tracking-widest shrink-0">
                        Dismiss
                    </button>
                </div>
            </div>
        </div>
    </template>
@endif
