{{-- ===== FLOATING BULK ACTION BAR =====
     Appears (teleported to body) when selectedIds.length > 0.
     Unchanged from original design.
--}}
@if ($canApprove)
    <template x-teleport="body">
        <div x-cloak x-show="selectedIds.length > 0"
            x-transition:enter="transition cubic-bezier(0.34, 1.56, 0.64, 1) duration-500"
            x-transition:enter-start="opacity-0 translate-y-32 scale-90"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-32 scale-90"
            class="fixed bottom-10 left-1/2 -translate-x-1/2 z-50 px-4">

            <div class="bg-slate-900 border border-slate-700/50 rounded-2xl px-6 py-4 shadow-2xl flex items-center gap-4">
                {{-- Count — PR style --}}
                <div class="flex items-center gap-2 pr-4 border-r border-slate-700">
                    <div class="h-7 w-7 rounded-lg bg-indigo-600 flex items-center justify-center">
                        <x-bx-check class="text-white w-4 h-4" />
                    </div>
                    <span class="text-sm font-black text-white" x-text="selectedIds.length + ' selected'"></span>
                </div>

                <div class="h-7 w-px bg-slate-700 mx-1 shrink-0"></div>

                <div class="flex items-center gap-2.5">
                    <button type="button" wire:click="loadSnapshot" wire:loading.attr="disabled"
                        class="h-9 px-5 rounded-xl bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest hover:bg-indigo-500 transition-all shadow-lg shadow-indigo-500/20">
                        <span wire:loading.remove wire:target="loadSnapshot">Review & Sign Selected</span>
                        <span wire:loading wire:target="loadSnapshot" class="flex items-center gap-1.5">
                            <x-bx-loader-alt class="animate-spin" /> Analyzing…
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
