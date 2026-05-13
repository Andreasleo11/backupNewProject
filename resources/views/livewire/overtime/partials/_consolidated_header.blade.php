{{-- ===== CONSOLIDATED VIEW HEADER ===== --}}
<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        {{-- Dark icon — matches PR index & overtime index --}}
        @php
            $backUrl = route('overtime.index') . ($backFilters ? '?' . http_build_query($backFilters) : '');
        @endphp
        <a href="{{ $backUrl }}"
            class="h-12 w-12 rounded-2xl bg-slate-900 flex items-center justify-center text-white shadow-lg shrink-0 hover:scale-105 hover:bg-slate-800 transition-all">
            <i class='bx bx-arrow-back text-2xl'></i>
        </a>
        
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-800">
                Consolidated Details
            </h1>
            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2 mt-0.5">
                {{ date('l, d M Y', strtotime($date)) }}
                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                <span>{{ $totalDetails }} Employees</span>
            </div>
        </div>
    </div>

    {{-- Search Bar (Primary) --}}
    <div class="flex-1 max-w-lg relative group">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <i class='bx bx-search text-slate-400 group-focus-within:text-indigo-500 transition-colors text-xl'></i>
        </div>
        <input type="text" wire:model.live.debounce.400ms="search"
            placeholder="Search employee, NIK, or task..."
            class="w-full bg-white border border-slate-200 rounded-2xl pl-12 pr-4 py-3 text-sm font-medium text-slate-800 focus:ring-4 focus:ring-indigo-50 focus:border-indigo-200 transition-all shadow-sm placeholder-slate-400">

        <div wire:loading wire:target="search" class="absolute inset-y-0 right-4 flex items-center">
            <div class="h-4 w-4 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></div>
        </div>
    </div>

    {{-- Secondary Controls --}}
    <div class="flex items-center gap-2 shrink-0">
        {{-- Department Filter --}}
        <div class="relative">
            <select wire:model.live="dept"
                class="appearance-none bg-white border border-slate-200 rounded-xl px-3 py-2 pr-8 text-sm font-medium text-slate-800 focus:ring-2 focus:ring-indigo-50 focus:border-indigo-200 transition-all shadow-sm cursor-pointer min-w-[160px]">
                <option value="">All Departments</option>
                @foreach ($departments as $department)
                    <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                @endforeach
            </select>
            <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none">
                <i class='bx bx-chevron-down text-slate-400 text-base'></i>
            </div>
        </div>

        {{-- View Mode Toggle --}}
        <button wire:click="toggleViewMode"
            class="h-10 w-10 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 transition-all flex items-center justify-center"
            title="{{ $viewMode === 'flattened' ? 'Switch to Grouped View' : 'Switch to Flattened View' }}">
            @if($viewMode === 'flattened')
                <i class='bx bx-list-ul text-lg'></i>
            @else
                <i class='bx bx-category text-lg'></i>
            @endif
        </button>

        {{-- Push All to JPayroll --}}
        @if($canPushToPayroll)
            <button wire:click="$dispatch('openPushAllConfirmation')"
                class="h-10 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 border border-indigo-600 text-white text-sm font-medium transition-all flex items-center gap-2"
                title="Push all eligible overtime data to JPayroll">
                <i class='bx bx-cloud-upload text-base'></i>
                <span class="hidden sm:inline">Push All</span>
            </button>
        @endif
    </div>

    {{-- Global Stats Pill --}}
    <div class="flex items-center bg-white rounded-2xl border border-slate-200/60 p-1.5 shadow-sm overflow-hidden shrink-0">
        <div class="flex items-center px-4 py-2 gap-2 border-r border-slate-100">
            <div class="h-6 w-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                <i class='bx bx-check-double text-xs'></i>
            </div>
            <div>
                <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Approved</div>
                <div class="text-sm font-black text-emerald-600 leading-none mt-1">{{ number_format($approvedDetails) }}</div>
            </div>
        </div>
        <div class="flex items-center px-4 py-2 gap-2 border-r border-slate-100">
            <div class="h-6 w-6 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">
                <i class='bx bx-time-five text-xs'></i>
            </div>
            <div>
                <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Pending</div>
                <div class="text-sm font-black text-amber-600 leading-none mt-1">{{ number_format($pendingDetails) }}</div>
            </div>
        </div>
        <div class="flex items-center px-4 py-2 gap-2">
            <div class="h-6 w-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
                <i class='bx bx-x text-xs'></i>
            </div>
            <div>
                <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Rejected</div>
                <div class="text-sm font-black text-rose-600 leading-none mt-1">{{ number_format($rejectedDetails) }}</div>
            </div>
        </div>
    </div>
</div>
