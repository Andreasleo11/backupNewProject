{{-- ===== STICKY TOOLBAR ===== --}}
{{-- Active filter count (non-tab filters only) --}}
@php
    $activeFilterCount = (int) !!$startDate + (int) !!$endDate + (int) !!$dept + (int) (!!$range);
@endphp

<div class="flex items-center gap-2">
    {{-- Search --}}
    <div class="relative flex-1">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
            <i class='bx bx-search text-base'></i>
        </span>
        <input type="text"
            class="block w-full rounded-xl border-0 bg-white py-2 pl-9 pr-8 text-sm text-slate-800 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-indigo-500 placeholder:text-slate-400 transition-shadow"
            placeholder="Search employee, NIK or ID…"
            wire:model.live.debounce.400ms="search">
        <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none"
            wire:loading wire:target="search,startDate,endDate,dept,infoStatus,clearFilter,resetFilters,setRange,perPage,sortBy,toggleGroupByDate">
            <i class='bx bx-loader-alt animate-spin text-indigo-500 text-base'></i>
        </div>
    </div>

    {{-- Quick date ranges (desktop only — on mobile they live in the filter panel) --}}
    <div class="hidden lg:flex items-center gap-0.5 rounded-xl bg-white border border-slate-200 p-0.5 shrink-0">
        @foreach (['today' => 'Today', '7d' => '7d', '30d' => '30d', 'mtd' => 'MTD'] as $k => $v)
            <button type="button" wire:click="setRange('{{ $k }}')"
                class="rounded-lg px-3 py-1.5 text-xs font-bold transition-all
                {{ $range === $k ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
                {{ $v }}
            </button>
        @endforeach
    </div>

    {{-- Filters slide-over trigger --}}
    <button @click="filtersOpen = true"
        class="relative inline-flex items-center gap-1.5 h-9 px-3 rounded-xl text-xs font-bold border transition-all shrink-0
        {{ $activeFilterCount > 0 ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
        <i class='bx bx-slider-alt text-sm'></i>
        <span class="hidden sm:inline">Filters</span>
        @if ($activeFilterCount > 0)
            <span class="flex h-4 w-4 items-center justify-center rounded-full bg-white/25 text-[9px] font-black leading-none">
                {{ $activeFilterCount }}
            </span>
        @endif
    </button>
</div>
