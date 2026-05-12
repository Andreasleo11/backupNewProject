{{-- ===== SLIM HEADER ===== --}}
<div class="flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm shadow-indigo-300/40">
            <i class='bx bx-time-five text-xl'></i>
        </div>
        <div>
            <h1 class="text-sm font-black text-slate-800 leading-tight tracking-tight">
                {{ $isPrivileged ? 'Overtime Requests' : 'My Overtime' }}
            </h1>
            <p class="text-[11px] text-slate-400 font-medium mt-0.5"
                wire:loading.class="opacity-40"
                wire:target="search,startDate,endDate,dept,infoStatus,clearFilter,resetFilters,setRange,perPage">
                {{ number_format($dataheader->total()) }} {{ $dataheader->total() === 1 ? 'record' : 'records' }}
            </p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        {{-- Secondary actions dropdown --}}
        <div class="relative" x-data="{ menuOpen: false }">
            <button @click="menuOpen = !menuOpen"
                class="h-9 w-9 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 hover:border-slate-300 transition-all"
                title="More options">
                <i class='bx bx-dots-horizontal-rounded text-lg'></i>
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
                @if ($isDetailReviewer || $isPrivileged)
                    <a href="{{ route('overtime.hub') }}"
                        class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                        <i class='bx bxs-zap text-indigo-500'></i> Switch to Hub
                    </a>
                @endif
                <a href="{{ route('overtime.bulk') }}"
                    class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-slate-700 hover:bg-slate-50 transition-colors">
                    <i class='bx bx-cloud-upload text-slate-400'></i> Smart Bulk Upload
                </a>
            </div>
        </div>

        {{-- Primary CTA --}}
        <a href="{{ route('overtime.create') }}"
            class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-black text-white shadow-sm shadow-indigo-300/40 hover:bg-indigo-700 active:scale-95 transition-all">
            <i class='bx bx-plus text-base'></i>
            <span class="hidden sm:inline">New Request</span>
        </a>
    </div>
</div>
