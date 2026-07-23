{{-- ===== SLIM HEADER & TOOLBAR — PR-synced style ===== --}}
@php
    $activeFilterCount = (int) !!$startDate + (int) !!$endDate + (int) !!$dept + (int) !!$range;
@endphp

<div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-5">
    {{-- 1. Identity --}}
    <div class="flex items-center gap-4 shrink-0">
        {{-- Dark icon — matches PR index --}}
        <div class="h-12 w-12 rounded-2xl bg-slate-900 flex items-center justify-center text-white shadow-lg shrink-0">
            <x-bx-time-five class="w-6 h-6" />
        </div>
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-800">
                Overtime Requests
            </h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2 mt-0.5">
                Management Hub
                @if ($infoStatus)
                    <span class="h-1 w-1 rounded-full bg-indigo-400"></span>
                    <span class="text-indigo-500">{{ str_replace('_', ' ', strtoupper($infoStatus)) }}</span>
                @endif
            </p>
        </div>
    </div>

    {{-- 2. Search & Toolbar --}}
    <div class="flex-1 w-full lg:max-w-2xl flex items-center gap-2">
        {{-- Search bar (PR-style: large, rounded-2xl, buttons inside) --}}
        <div class="relative flex-1 group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <x-bx-search class="text-slate-400 group-focus-within:text-indigo-500 transition-colors w-5 h-5" />
            </div>

            <input type="text" wire:model.live.debounce.400ms="search"
                placeholder="Find overtime by employee, NIK or ID…"
                class="w-full bg-white border border-slate-200 rounded-2xl pl-12 pr-28 py-3 text-sm font-medium text-slate-800 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-200 transition-all shadow-sm placeholder-slate-400">

            {{-- Right-side controls inside search --}}
            <div class="absolute inset-y-0 right-2 flex items-center gap-1">
                {{-- Loading indicator --}}
                <div wire:loading wire:target="search,startDate,endDate,dept,infoStatus,clearFilter,resetFilters,setRange,perPage,sortBy,toggleGroupByDate"
                    class="h-9 w-9 flex items-center justify-center">
                    <div class="h-4 w-4 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
                </div>

                {{-- Filters button --}}
                <button type="button" @click="filtersOpen = true"
                    class="h-9 px-3 rounded-xl flex items-center gap-1.5 transition-all text-[10px] font-bold uppercase tracking-tight
                    {{ $activeFilterCount > 0 ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-100 hover:text-slate-600' }}">
                    <x-bx-slider-alt class="w-4 h-4" />
                    @if ($activeFilterCount > 0)
                        <span>{{ $activeFilterCount }}</span>
                    @endif
                </button>

                {{-- Per-page --}}
                <select wire:model.live="perPage"
                    class="h-9 rounded-xl border-slate-200 bg-white text-[10px] font-black text-slate-700 focus:ring-indigo-500">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
        </div>
    </div>

    {{-- 3. Actions --}}
    <div class="flex items-center gap-2 shrink-0">
        {{-- Secondary actions dropdown --}}
        <div class="relative" x-data="{ menuOpen: false }">
            <button @click="menuOpen = !menuOpen"
                class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:border-slate-300 transition-all"
                title="More options">
                <x-bx-dots-horizontal-rounded class="w-5 h-5" />
            </button>
            <div x-show="menuOpen" @click.outside="menuOpen = false"
                x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-full mt-1.5 w-48 rounded-xl bg-white border border-slate-200/80 shadow-xl shadow-slate-200/60 z-30 py-1 overflow-hidden"
                x-cloak>
                @can('overtime.approve')
                    <a href="{{ route('overtime.hub') }}"
                        class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                        <x-bxs-zap class="text-indigo-500" /> Switch to Hub
                    </a>
                @endcan
                <a href="{{ route('overtime.bulk') }}"
                    class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    <x-bx-cloud-upload class="text-slate-400" /> Smart Bulk Upload
                </a>
            </div>
        </div>

        {{-- Primary CTA --}}
        <a href="{{ route('overtime.create') }}"
            class="h-10 px-5 rounded-2xl bg-indigo-600 flex items-center gap-2 text-white shadow-lg shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all font-bold text-sm uppercase tracking-widest whitespace-nowrap">
            <x-bx-plus-circle class="w-5 h-5" />
            <span class="hidden sm:inline">New Request</span>
        </a>
    </div>
</div>
