<div x-data="{
        open: @entangle('isOpen'),
        selectedIndex: 0,
        resultsCount: {{ count($results) }},
        init() {
            window.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                    e.preventDefault();
                    this.open = !this.open;
                    if (this.open) {
                        $nextTick(() => this.$refs.searchInput.focus());
                    }
                }
                if (e.key === 'Escape' && this.open) {
                    this.open = false;
                }
            });
        },
        navigate(url) {
            this.open = false;
            window.location.href = url;
        }
    }"
    @open-cmd-k.window="open = true; $nextTick(() => $refs.searchInput.focus())">

    <div x-show="open" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[120] overflow-y-auto p-4 sm:p-6 md:p-20"
        role="dialog" aria-modal="true" x-cloak>

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-md transition-opacity" @click="open = false"></div>

        {{-- Command Dialog Window --}}
        <div x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 -translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-y-4"
            class="relative mx-auto max-w-2xl transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl ring-1 ring-slate-900/10 transition-all border border-slate-100">

            {{-- Search Bar Input --}}
            <div class="relative flex items-center px-6 border-b border-slate-100">
                <svg class="h-5 w-5 text-slate-400 shrink-0 mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input x-ref="searchInput" 
                    type="text" 
                    wire:model.live.debounce.150ms="query"
                    placeholder="Type a command or search system navigation... (Press ESC to close)"
                    class="w-full bg-transparent py-5 text-base font-semibold text-slate-800 placeholder-slate-400 outline-none border-none ring-0 focus:ring-0">
                <span class="hidden sm:inline-block rounded-lg bg-slate-100 px-2 py-1 text-xs font-bold text-slate-400 shadow-sm border border-slate-200">ESC</span>
            </div>

            {{-- Results Section --}}
            <div class="max-h-96 overflow-y-auto px-4 py-4 custom-scrollbar">
                @if (count($results) > 0)
                    <div class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400 px-3 mb-2">
                        {{ empty($query) ? 'Quick Navigation Shortcuts' : 'Matching Results' }}
                    </div>
                    <div class="space-y-1">
                        @foreach ($results as $index => $item)
                            <a href="{{ route($item['route'], $item['params'] ?? []) }}"
                                @click.prevent="navigate('{{ route($item['route'], $item['params'] ?? []) }}')"
                                class="flex items-center justify-between gap-3 rounded-2xl px-4 py-3 text-sm font-semibold transition-all duration-150 group hover:bg-blue-50/80 hover:text-blue-700 text-slate-700">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-500 group-hover:bg-blue-600 group-hover:text-white transition-colors shrink-0">
                                        @include('new.layouts.partials.nav-icon', ['name' => $item['icon']])
                                    </div>
                                    <div class="flex flex-col min-w-0">
                                        <span class="truncate text-sm font-bold text-slate-900 group-hover:text-blue-700 leading-tight">
                                            {{ $item['label'] }}
                                        </span>
                                        @if (isset($item['parent_label']))
                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 leading-tight mt-0.5">
                                                {{ $item['parent_label'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-xs font-bold text-slate-400 opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1">
                                    Jump to <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="py-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-3">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                        </div>
                        <p class="text-sm font-bold text-slate-700">No navigation items found</p>
                        <p class="text-xs font-medium text-slate-400 mt-1">Try searching for another feature or module name.</p>
                    </div>
                @endif
            </div>

            {{-- Footer Helper Bar --}}
            <div class="flex items-center justify-between px-6 py-3 bg-slate-50 border-t border-slate-100 text-xs font-bold text-slate-400">
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1.5"><span class="rounded bg-white px-1.5 py-0.5 shadow-sm border text-[10px]">Ctrl K</span> Toggle</span>
                    <span class="flex items-center gap-1.5"><span class="rounded bg-white px-1.5 py-0.5 shadow-sm border text-[10px]">ESC</span> Close</span>
                </div>
                <div>
                    <span class="text-blue-600 font-extrabold">{{ config('app.name') }}</span> Command Bar
                </div>
            </div>
        </div>
    </div>
</div>
