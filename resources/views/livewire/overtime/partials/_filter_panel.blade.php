{{-- ===== FILTER SLIDE-OVER PANEL =====
     Triggered by the ⚙ Filters button in _toolbar.blade.php.
     All advanced controls live here: date range, dept, review state,
     per-page, group-by, export, bulk upload.
     Active filter chips are shown as a summary at the top.
--}}
<template x-teleport="body">
    <div x-cloak x-show="filtersOpen" class="relative z-[80]">

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-slate-900/25 backdrop-blur-sm"
            x-show="filtersOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="filtersOpen = false">
        </div>

        {{-- Panel --}}
        <div class="fixed inset-y-0 right-0 z-[90] w-full max-w-xs flex"
            x-show="filtersOpen"
            x-transition:enter="transform transition ease-in-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transform transition ease-in-out duration-300"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full">

            <div class="w-full bg-white shadow-2xl flex flex-col border-l border-slate-200/60">

                {{-- Panel Header --}}
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 shrink-0">
                    <div class="flex items-center gap-2 text-slate-700">
                        <i class='bx bx-slider-alt text-base'></i>
                        <span class="text-sm font-black">Filters & Options</span>
                    </div>
                    <button @click="filtersOpen = false"
                        class="h-8 w-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-all">
                        <i class='bx bx-x text-xl'></i>
                    </button>
                </div>

                {{-- Panel Body --}}
                <div class="flex-1 overflow-y-auto px-5 py-5 space-y-5">

                    {{-- Active filter chips summary --}}
                    @php
                        $anyChip = $range || ($startDate && $endDate) || $startDate || $dept || $search;
                    @endphp
                    @if ($anyChip)
                        <div class="space-y-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Active Filters</p>
                            <div class="flex flex-wrap gap-1.5">
                                @if ($range)
                                    <button wire:click="clearFilter('range')"
                                        class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 border border-indigo-100 px-2 py-1 text-[10px] font-black text-indigo-700 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all">
                                        {{ strtoupper($range) }} <i class='bx bx-x text-xs'></i>
                                    </button>
                                @endif
                                @if ($startDate && $endDate)
                                    <button wire:click="clearFilter('dates')"
                                        class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 border border-indigo-100 px-2 py-1 text-[10px] font-black text-indigo-700 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all">
                                        {{ date('d M y', strtotime($startDate)) }} – {{ date('d M y', strtotime($endDate)) }} <i class='bx bx-x text-xs'></i>
                                    </button>
                                @endif
                                @if ($dept)
                                    <button wire:click="clearFilter('dept')"
                                        class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 border border-indigo-100 px-2 py-1 text-[10px] font-black text-indigo-700 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all">
                                        {{ collect($departments)->firstWhere('id', $dept)['name'] ?? 'Dept' }} <i class='bx bx-x text-xs'></i>
                                    </button>
                                @endif
                                @if ($search)
                                    <button wire:click="clearFilter('search')"
                                        class="inline-flex items-center gap-1 rounded-lg bg-indigo-50 border border-indigo-100 px-2 py-1 text-[10px] font-black text-indigo-700 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all">
                                        "{{ Str::limit($search, 12) }}" <i class='bx bx-x text-xs'></i>
                                    </button>
                                @endif
                            </div>
                            <button wire:click="resetFilters; filtersOpen = false"
                                class="text-[10px] font-black text-slate-400 hover:text-rose-500 transition-colors uppercase tracking-widest">
                                <i class='bx bx-refresh text-xs'></i> Reset All
                            </button>
                        </div>
                        <div class="h-px bg-slate-100"></div>
                    @endif

                    {{-- Time Period --}}
                    <div class="space-y-2.5">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Time Period</p>

                        {{-- Quick range pills (visible on all screen sizes inside panel) --}}
                        <div class="grid grid-cols-4 gap-1">
                            @foreach (['today' => 'Today', '7d' => '7d', '30d' => '30d', 'mtd' => 'MTD'] as $k => $v)
                                <button type="button" wire:click="setRange('{{ $k }}')"
                                    class="rounded-lg py-1.5 text-xs font-bold text-center transition-all
                                    {{ $range === $k ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100' }}">
                                    {{ $v }}
                                </button>
                            @endforeach
                        </div>

                        {{-- Custom date range — flatpickr --}}
                        <div class="flex items-center gap-1.5"
                            wire:ignore
                            x-data="{ fpStart: null, fpEnd: null }"
                            x-init="
                                $nextTick(() => {
                                    fpStart = window.flatpickr($refs.fpStart, {
                                        dateFormat: 'Y-m-d',
                                        defaultDate: $wire.startDate || null,
                                        disableMobile: true,
                                        onChange([d], str) {
                                            $wire.set('startDate', str || null);
                                            if (fpEnd) fpEnd.set('minDate', str || null);
                                        }
                                    });
                                    fpEnd = window.flatpickr($refs.fpEnd, {
                                        dateFormat: 'Y-m-d',
                                        defaultDate: $wire.endDate || null,
                                        disableMobile: true,
                                        onChange([d], str) {
                                            $wire.set('endDate', str || null);
                                            if (fpStart) fpStart.set('maxDate', str || null);
                                        }
                                    });
                                    $wire.$watch('startDate', val => {
                                        if (fpStart) fpStart.setDate(val || '', false);
                                        if (fpEnd)   fpEnd.set('minDate', val || null);
                                    });
                                    $wire.$watch('endDate', val => {
                                        if (fpEnd)   fpEnd.setDate(val || '', false);
                                        if (fpStart) fpStart.set('maxDate', val || null);
                                    });
                                })
                            ">
                            <div class="relative flex-1">
                                <input x-ref="fpStart" type="text" placeholder="Start date" readonly
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-2.5 pr-7 text-xs font-medium text-slate-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow">
                                <span class="absolute inset-y-0 right-2 flex items-center pointer-events-none text-slate-300">
                                    <i class='bx bx-calendar text-sm'></i>
                                </span>
                            </div>
                            <span class="text-slate-300 text-xs font-bold shrink-0">→</span>
                            <div class="relative flex-1">
                                <input x-ref="fpEnd" type="text" placeholder="End date" readonly
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-2.5 pr-7 text-xs font-medium text-slate-700 cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow">
                                <span class="absolute inset-y-0 right-2 flex items-center pointer-events-none text-slate-300">
                                    <i class='bx bx-calendar text-sm'></i>
                                </span>
                            </div>
                        </div>
                        @error('endDate')
                            <p class="text-xs text-rose-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Department (privileged) --}}
                    @if ($isPrivileged)
                        <div class="space-y-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Department</p>
                            <select wire:model.live="dept"
                                class="w-full rounded-xl border-slate-200 bg-slate-50 py-2 px-3 text-xs font-medium text-slate-700 focus:ring-indigo-500">
                                <option value="">All Departments</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="h-px bg-slate-100"></div>

                    {{-- View Options --}}
                    <div class="space-y-4">
                        <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">View Options</p>

                        {{-- Group by date toggle --}}
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-700">Group by Date</span>
                                <p class="text-[10px] text-slate-400 mt-0.5">Collapse same-day forms</p>
                            </div>
                            <button type="button" wire:click="toggleGroupByDate"
                                class="relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors
                                {{ $groupByDate ? 'bg-indigo-600' : 'bg-slate-200' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform
                                    {{ $groupByDate ? 'translate-x-[18px]' : 'translate-x-0.5' }}">
                                </span>
                            </button>
                        </div>

                        {{-- Per page --}}
                        <div class="space-y-1.5">
                            <span class="text-xs font-bold text-slate-700">Rows per Page</span>
                            <div class="flex items-center gap-3">
                                @foreach ([10, 25, 50] as $n)
                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                        <input type="radio" wire:model.live="perPage" value="{{ $n }}"
                                            class="h-3.5 w-3.5 text-indigo-600 border-slate-300 focus:ring-indigo-500">
                                        <span class="text-xs font-bold text-slate-600">{{ $n }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Tools (privileged) --}}
                    @if ($isPrivileged)
                        <div class="h-px bg-slate-100"></div>
                        <div class="space-y-2">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tools</p>
                            <button type="button" wire:click="exportCsv" wire:loading.attr="disabled"
                                class="w-full flex items-center gap-2.5 rounded-xl bg-slate-50 border border-slate-200 px-3 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-100 transition-all">
                                <i class='bx bx-export text-slate-400 text-base'></i>
                                <span wire:loading.remove wire:target="exportCsv">Export CSV</span>
                                <span wire:loading wire:target="exportCsv" class="flex items-center gap-1.5">
                                    <i class='bx bx-loader-alt animate-spin text-indigo-500'></i> Exporting…
                                </span>
                            </button>
                        </div>
                    @endif

                </div>

                {{-- Panel Footer --}}
                <div class="shrink-0 px-5 py-4 border-t border-slate-100 bg-slate-50/50">
                    <button @click="filtersOpen = false"
                        class="w-full h-9 rounded-xl bg-indigo-600 text-xs font-black text-white hover:bg-indigo-700 transition-all shadow-sm shadow-indigo-300/40">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
