{{-- ===== FLOATING BULK ACTION BAR ===== --}}
@if ($viewMode === 'grouped')
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
                {{-- Count --}}
                <div class="flex items-center gap-2 pr-4 border-r border-slate-700">
                    <div class="h-7 w-7 rounded-lg bg-indigo-600 flex items-center justify-center">
                        <x-bx-check class="text-white w-4 h-4" />
                    </div>
                    {{-- Filter unique forms selected since multiple employees from same form could be selected --}}
                    <span class="text-sm font-black text-white" x-text="[...new Set(selectedIds)].length + ' Forms Selected'"></span>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2">
                    <button wire:click="signSelected"
                        wire:loading.attr="disabled"
                        wire:target="signSelected"
                        class="h-9 px-4 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white text-xs font-black uppercase tracking-widest shadow-lg shadow-emerald-900/20 transition-all flex items-center gap-2 disabled:opacity-50">
                        <span wire:loading.remove wire:target="signSelected">
                            <x-bx-check-double class="w-4 h-4" />
                            Sign Selected
                        </span>
                        <span wire:loading wire:target="signSelected">
                            <x-bx-loader-alt class="animate-spin w-4 h-4" />
                            Signing...
                        </span>
                    </button>
                    
                    <button @click="selectedIds = []"
                        class="h-9 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-black uppercase tracking-widest transition-all">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </template>
@endif
