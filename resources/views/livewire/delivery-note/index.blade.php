@section('page-title', 'Delivery Notes')
@section('page-subtitle', 'Manage logistics records, assignments, and approval stages.')

<div x-data="{ showFilters: false }" class="max-w-7xl mx-auto px-4 py-6 space-y-6">

    {{-- Flash message --}}
    @if (session()->has('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm flex items-start gap-3">
            <svg class="h-5 w-5 text-emerald-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <div class="flex-1 font-medium">{{ session('success') }}</div>
        </div>
    @endif

    {{-- Header Content --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                Delivery Notes
            </h1>
            <p class="mt-1 text-sm font-medium text-slate-500">
                Manage logistics records, assignments, and approval stages.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('delivery-notes.create') }}"
                class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-md shadow-indigo-500/20 hover:bg-indigo-700 hover:shadow-lg transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <span class="text-lg leading-none">+</span>
                Create Delivery Note
            </a>
        </div>
    </div>

    {{-- Toolbar / Search & Filter Toggle --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="relative w-full lg:max-w-md group">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-4 w-4 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>
            <input type="text" wire:model.live.debounce.400ms="searchAll"
                placeholder="Search number, driver, vehicle..."
                class="block w-full rounded-lg border-slate-300 bg-white pl-9 px-3 py-2.5 text-sm text-slate-900 shadow-sm transition
                       focus:border-indigo-500 focus:ring-indigo-500 placeholder:text-slate-400 focus:shadow-md">
        </div>

        <button @click="showFilters = !showFilters" type="button" 
            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
            Advanced Filters
            @if ($filterStatus !== 'all' || $filterBranch !== 'all' || $filterRitasi !== 'all' || $fromDate || $toDate)
                <span class="flex h-2 w-2 rounded-full bg-indigo-500 ring-4 ring-indigo-50"></span>
            @endif
        </button>
    </div>

    {{-- Collapsible Filters Panel --}}
    <div x-show="showFilters" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
         style="display: none;" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
         
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                <svg class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                Filter Criteria
            </h2>
            <button type="button" wire:click="resetFilters" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 underline underline-offset-2">
                Reset All Filters
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
            {{-- Status --}}
            <div>
                <label class="block text-xs font-semibold tracking-wide text-slate-600 mb-1.5 uppercase">Status</label>
                <select wire:model.defer="inputStatus" class="block w-full rounded-lg border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-sm focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all">All Statuses</option>
                    <option value="draft">Draft</option>
                    <option value="submitted">Submitted</option>
                </select>
            </div>

            {{-- Branch --}}
            <div>
                <label class="block text-xs font-semibold tracking-wide text-slate-600 mb-1.5 uppercase">Branch</label>
                <select wire:model.defer="inputBranch" class="block w-full rounded-lg border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-sm focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all">All Branches</option>
                    <option value="JAKARTA">JAKARTA</option>
                    <option value="KARAWANG">KARAWANG</option>
                </select>
            </div>

            {{-- Ritasi --}}
            <div>
                <label class="block text-xs font-semibold tracking-wide text-slate-600 mb-1.5 uppercase">Ritasi</label>
                <select wire:model.defer="inputRitasi" class="block w-full rounded-lg border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-sm focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="all">All Ritasi</option>
                    <option value="1">1 (Pagi)</option>
                    <option value="2">2 (Siang)</option>
                    <option value="3">3 (Sore)</option>
                    <option value="4">4 (Malam)</option>
                </select>
            </div>

            {{-- From Date --}}
            <div>
                <label class="block text-xs font-semibold tracking-wide text-slate-600 mb-1.5 uppercase">From Date</label>
                <input type="date" wire:model.defer="inputFromDate" class="block w-full rounded-lg border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-sm focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            {{-- To Date --}}
            <div>
                <label class="block text-xs font-semibold tracking-wide text-slate-600 mb-1.5 uppercase">To Date</label>
                <input type="date" wire:model.defer="inputToDate" class="block w-full rounded-lg border-slate-300 bg-slate-50 px-3 py-2 text-sm text-slate-700 shadow-sm focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
            </div>
        </div>

        <div class="mt-6 flex justify-end border-t border-slate-100 pt-5">
            <button type="button" wire:click="applyFilters" @click="showFilters = false" class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-lg bg-slate-900 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-slate-800 transition focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                Apply Selected Filters
            </button>
        </div>
    </div>

    {{-- Active Filters Summary --}}
    @if ($filterStatus !== 'all' || $filterBranch !== 'all' || $filterRitasi !== 'all' || $fromDate || $toDate || $searchAll)
        <div class="flex flex-wrap items-center gap-2 mt-2">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mr-2">Active Rules:</span>
            
            @if ($filterStatus !== 'all')
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 border border-slate-200 shadow-sm text-xs">
                    <span class="text-slate-500 font-medium">Status</span>
                    <span class="font-bold text-slate-800">{{ ucfirst($filterStatus) }}</span>
                </span>
            @endif

            @if ($filterBranch !== 'all')
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 border border-slate-200 shadow-sm text-xs">
                    <span class="text-slate-500 font-medium">Branch</span>
                    <span class="font-bold text-slate-800">{{ $filterBranch }}</span>
                </span>
            @endif

            @if ($filterRitasi !== 'all')
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 border border-slate-200 shadow-sm text-xs">
                    <span class="text-slate-500 font-medium">Ritasi</span>
                    <span class="font-bold text-slate-800">{{ $filterRitasi }}</span>
                </span>
            @endif

            @if ($fromDate)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 border border-slate-200 shadow-sm text-xs">
                    <span class="text-slate-500 font-medium">From</span>
                    <span class="font-bold text-slate-800">{{ $fromDate }}</span>
                </span>
            @endif

            @if ($toDate)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-white px-3 py-1 border border-slate-200 shadow-sm text-xs">
                    <span class="text-slate-500 font-medium">To</span>
                    <span class="font-bold text-slate-800">{{ $toDate }}</span>
                </span>
            @endif

            @if ($searchAll)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 border border-indigo-100 shadow-sm text-xs">
                    <span class="text-indigo-500 font-medium">Search</span>
                    <span class="font-bold text-indigo-800">"{{ $searchAll }}"</span>
                </span>
            @endif
        </div>
    @endif

    {{-- Main Table Area --}}
    <div class="rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col mt-4">
        <div class="border-b border-slate-100 bg-slate-50/50 px-5 py-3.5 flex items-center justify-between">
            <span class="text-sm font-bold text-slate-800 tracking-tight">
                Logistics Database
            </span>
            <span class="text-xs font-semibold bg-white border border-slate-200 py-1 px-3 rounded-full text-slate-500 shadow-sm">
                {{ $deliveryNotes->total() }} total records
            </span>
        </div>

        <div class="overflow-x-auto">
            @include('livewire.delivery-note._table')
        </div>

        @if($deliveryNotes->hasPages())
            <div class="border-t border-slate-100 px-5 py-3 bg-white">
                {{ $deliveryNotes->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

</div>
