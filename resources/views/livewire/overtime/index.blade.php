@section('title', 'Form Overtime List')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-5" x-data="{ mobileFiltersOpen: false, deleteOpen: false }"
    x-on:show-delete-modal.window="deleteOpen = true" x-on:hide-delete-modal.window="deleteOpen = false">

    {{-- ================= HEADER ================= --}}
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-lg sm:text-xl font-semibold text-slate-900">
                Form Overtime List
            </h1>
            <p class="mt-1 text-xs sm:text-sm text-slate-500">
                Pantau dan kelola pengajuan lembur berdasarkan departemen, status, dan rentang tanggal.
            </p>
        </div>

        @if (Auth::user()->department->name !== 'MANAGEMENT')
            <a href="{{ route('overtime.create') }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-2 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                </svg>
                <span>Create Form Overtime</span>
            </a>
        @endif
    </div>

    {{-- Breadcrumb --}}
    <nav class="text-xs text-slate-500" aria-label="Breadcrumb">
        <ol class="flex items-center gap-1">
            <li>
                <a href="{{ route('overtime.index') }}" class="hover:text-slate-700">
                    Form Overtime
                </a>
            </li>
            <li class="text-slate-400">/</li>
            <li class="font-medium text-slate-700">
                List
            </li>
        </ol>
    </nav>

    {{-- ================= INFO SUMMARY ================= --}}
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                Info Summary
            </span>

            {{-- Scope switch (All results vs This page) --}}
            <div class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 p-0.5 text-[11px]">
                <label
                    class="inline-flex cursor-pointer items-center rounded-full px-2.5 py-1.5 transition
                           {{ $statsScope === 'all' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    <input type="radio" value="all" class="sr-only" wire:model.live="statsScope">
                    <span>All results</span>
                </label>
                <label
                    class="inline-flex cursor-pointer items-center rounded-full px-2.5 py-1.5 transition
                           {{ $statsScope === 'page' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    <input type="radio" value="page" class="sr-only" wire:model.live="statsScope">
                    <span>This page</span>
                </label>
            </div>
        </div>

        {{-- Skeleton while stats recompute --}}
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3" wire:loading
            wire:target="startDate,endDate,dept,infoStatus,isPush,search,range,perPage,statsScope">
            @for ($i = 0; $i < 3; $i++)
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm animate-pulse">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-slate-100"></div>
                        <div class="flex-1 space-y-2">
                            <div class="h-3 w-24 rounded bg-slate-100"></div>
                            <div class="h-4 w-16 rounded bg-slate-100"></div>
                            <div class="h-1.5 w-full rounded-full bg-slate-100"></div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>

        {{-- Cards (visible when NOT loading) --}}
        <div class="grid grid-cols-1 gap-3 md:grid-cols-3" wire:loading.remove
            wire:target="startDate,endDate,dept,infoStatus,isPush,search,range,perPage,statsScope">
            {{-- Approved --}}
            <button type="button" wire:click="setInfoFilter('approved')"
                aria-pressed="{{ $infoStatus === 'approved' ? 'true' : 'false' }}"
                class="group flex w-full rounded-xl border bg-white px-4 py-3 text-left shadow-sm transition
                           hover:border-slate-300 hover:shadow-md
                           {{ $infoStatus === 'approved' ? 'border-indigo-500 ring-1 ring-indigo-500 bg-indigo-50/40' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                        ✓
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-medium text-slate-500">Approved</div>
                        <div class="mt-0.5 text-lg font-semibold text-slate-900">
                            {{ number_format($stats['approved']) }}
                        </div>
                        <div class="mt-2 h-1.5 w-full rounded-full bg-slate-100">
                            <div class="h-1.5 rounded-full bg-emerald-500" style="width: {{ $stats['pct_approved'] }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </button>

            {{-- Rejected --}}
            <button type="button" wire:click="setInfoFilter('rejected')"
                aria-pressed="{{ $infoStatus === 'rejected' ? 'true' : 'false' }}"
                class="group flex w-full rounded-xl border bg-white px-4 py-3 text-left shadow-sm transition
                           hover:border-slate-300 hover:shadow-md
                           {{ $infoStatus === 'rejected' ? 'border-rose-500 ring-1 ring-rose-500 bg-rose-50/60' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-rose-50 text-rose-600">
                        ✕
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-medium text-slate-500">Rejected</div>
                        <div class="mt-0.5 text-lg font-semibold text-slate-900">
                            {{ number_format($stats['rejected']) }}
                        </div>
                        <div class="mt-2 h-1.5 w-full rounded-full bg-slate-100">
                            <div class="h-1.5 rounded-full bg-rose-500" style="width: {{ $stats['pct_rejected'] }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </button>

            {{-- Pending --}}
            <button type="button" wire:click="setInfoFilter('pending')"
                aria-pressed="{{ $infoStatus === 'pending' ? 'true' : 'false' }}"
                class="group flex w-full rounded-xl border bg-white px-4 py-3 text-left shadow-sm transition
                           hover:border-slate-300 hover:shadow-md
                           {{ $infoStatus === 'pending' ? 'border-amber-500 ring-1 ring-amber-500 bg-amber-50/60' : '' }}">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-600">
                        ⏳
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-medium text-slate-500">Pending</div>
                        <div class="mt-0.5 text-lg font-semibold text-slate-900">
                            {{ number_format($stats['pending']) }}
                        </div>
                        <div class="mt-2 h-1.5 w-full rounded-full bg-slate-100">
                            <div class="h-1.5 rounded-full bg-slate-500" style="width: {{ $stats['pct_pending'] }}%">
                            </div>
                        </div>
                    </div>
                </div>
            </button>
        </div>
    </div>

    {{-- ================= FILTERS / TOOLBAR CARD ================= --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="p-4 sm:p-5 space-y-3">

            {{-- Top row: Search + quick ranges + per-page + density + mobile filters --}}
            <div class="flex flex-wrap items-center gap-2">

                {{-- Search --}}
                <div class="relative w-full max-w-md">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M8.5 3.5a5 5 0 013.977 8.08l3.221 3.222a.75.75 0 11-1.06 1.06l-3.222-3.221A5 5 0 118.5 3.5zm0 1.5a3.5 3.5 0 100 7 3.5 3.5 0 000-7z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                    <input type="text"
                        class="block w-full rounded-xl border border-slate-300 bg-white py-2 pl-8 pr-3 text-xs sm:text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        placeholder="Search ID / Admin / Branch" wire:model.live.debounce.400ms="search">
                </div>

                {{-- Quick date ranges (desktop) --}}
                <div class="hidden md:flex items-center gap-1">
                    <button type="button"
                        class="inline-flex items-center rounded-full px-3 py-1.5 text-[11px] font-medium transition
                                   {{ $range === 'today' ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                        aria-pressed="{{ $range === 'today' ? 'true' : 'false' }}" wire:click="setRange('today')">
                        Today
                    </button>
                    <button type="button"
                        class="inline-flex items-center rounded-full px-3 py-1.5 text-[11px] font-medium transition
                                   {{ $range === '7d' ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                        aria-pressed="{{ $range === '7d' ? 'true' : 'false' }}" wire:click="setRange('7d')">
                        Last 7 days
                    </button>
                    <button type="button"
                        class="inline-flex items-center rounded-full px-3 py-1.5 text-[11px] font-medium transition
                                   {{ $range === '30d' ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                        aria-pressed="{{ $range === '30d' ? 'true' : 'false' }}" wire:click="setRange('30d')">
                        Last 30 days
                    </button>
                    <button type="button"
                        class="inline-flex items-center rounded-full px-3 py-1.5 text-[11px] font-medium transition
                                   {{ $range === 'mtd' ? 'bg-slate-900 text-white' : 'bg-slate-50 text-slate-600 hover:bg-slate-100' }}"
                        aria-pressed="{{ $range === 'mtd' ? 'true' : 'false' }}" wire:click="setRange('mtd')"
                        title="Month-to-Date">
                        MTD
                    </button>
                </div>

                {{-- Right tools --}}
                <div class="ml-auto flex items-center gap-2">

                    {{-- Per-page --}}
                    <select
                        class="rounded-lg border border-slate-300 bg-white py-1.5 pl-3 pr-8 text-xs text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        wire:model.live="perPage">
                        <option value="10">10 / page</option>
                        <option value="25">25 / page</option>
                        <option value="50">50 / page</option>
                    </select>

                    {{-- Density toggle --}}
                    <div class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 text-[11px]">
                        <label
                            class="inline-flex cursor-pointer items-center gap-1 rounded-l-lg px-2.5 py-1.5 transition
                                   {{ $dense ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                            <input type="radio" value="1" class="sr-only" wire:model.live="dense">
                            <span>Compact</span>
                        </label>
                        <label
                            class="inline-flex cursor-pointer items-center gap-1 rounded-r-lg px-2.5 py-1.5 transition
                                   {{ !$dense ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                            <input type="radio" value="0" class="sr-only" wire:model.live="dense">
                            <span>Comfort</span>
                        </label>
                    </div>

                    {{-- Mobile: open filters sheet --}}
                    <button type="button"
                        class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 md:hidden"
                        @click="mobileFiltersOpen = true">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path
                                d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4A1 1 0 013 5zm2 5a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zm3 4a1 1 0 011-1h4a1 1 0 110 2h-4a1 1 0 01-1-1z" />
                        </svg>
                        Filters
                    </button>
                </div>
            </div>

            {{-- Desktop filter fields (inline) --}}
            <div class="mt-3 hidden flex-wrap items-end gap-3 md:flex">
                <div>
                    <label for="start_date" class="block text-[11px] font-medium text-slate-600">Start Date</label>
                    <input id="start_date" type="date"
                        class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        wire:model.live="startDate">
                    @error('startDate')
                        <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-[11px] font-medium text-slate-600">End Date</label>
                    <input id="end_date" type="date"
                        class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        wire:model.live="endDate">
                    @error('endDate')
                        <p class="mt-1 text-[11px] text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="dept" class="block text-[11px] font-medium text-slate-600">Department</label>
                    <select id="dept"
                        class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        wire:model.live="dept">
                        <option value="">-- All --</option>
                        @foreach ($departments as $d)
                            <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                @if ($user->specification->name === 'VERIFICATOR')
                    <div>
                        <label for="info_status" class="block text-[11px] font-medium text-slate-600">Info</label>
                        <select id="info_status"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                            wire:model.live="infoStatus">
                            <option value="">-- Semua --</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>

                    <div>
                        <label for="is_push" class="block text-[11px] font-medium text-slate-600">Push by
                            Verificator</label>
                        <select id="is_push"
                            class="mt-1 block w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs text-slate-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                            wire:model.live="isPush">
                            <option value="">-- All --</option>
                            <option value="1">Already Pushed</option>
                            <option value="0">Not Yet Pushed</option>
                        </select>
                    </div>
                @endif

                <div class="ml-auto flex items-center gap-2">
                    <button type="button"
                        class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50"
                        wire:click="resetFilters" wire:loading.attr="disabled">
                        <span class="text-slate-400">⟲</span>
                        Clear all
                    </button>

                    {{-- Export CSV --}}
                    <button type="button"
                        class="inline-flex items-center gap-1 rounded-lg border border-indigo-500 bg-indigo-50 px-3 py-1.5 text-xs font-medium text-indigo-700 shadow-sm hover:bg-indigo-100"
                        wire:click="exportCsv" wire:loading.attr="disabled">
                        <span>⬇</span>
                        Export CSV
                    </button>
                </div>
            </div>

            {{-- Applied filters chips --}}
            <div class="mt-2 flex flex-wrap gap-2">
                @if ($range)
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] text-slate-700">
                        Range: {{ strtoupper($range) }}
                        <button type="button" class="text-slate-400 hover:text-slate-600"
                            wire:click="clearFilter('range')">✕</button>
                    </span>
                @endif

                @if ($startDate && $endDate)
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] text-slate-700">
                        {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} –
                        {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                        <button type="button" class="text-slate-400 hover:text-slate-600"
                            wire:click="clearFilter('dates')">✕</button>
                    </span>
                @endif

                @if ($dept)
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] text-slate-700">
                        Dept: {{ collect($departments)->firstWhere('id', $dept)['name'] ?? $dept }}
                        <button type="button" class="text-slate-400 hover:text-slate-600"
                            wire:click="clearFilter('dept')">✕</button>
                    </span>
                @endif

                @if ($infoStatus)
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] text-slate-700">
                        Info: {{ ucfirst($infoStatus) }}
                        <button type="button" class="text-slate-400 hover:text-slate-600"
                            wire:click="clearFilter('infoStatus')">✕</button>
                    </span>
                @endif

                @if ($isPush !== null && $isPush !== '')
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] text-slate-700">
                        {{ $isPush === '1' ? 'Pushed' : 'Not pushed' }}
                        <button type="button" class="text-slate-400 hover:text-slate-600"
                            wire:click="clearFilter('isPush')">✕</button>
                    </span>
                @endif

                @if ($search)
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] text-slate-700">
                        Search: “{{ $search }}”
                        <button type="button" class="text-slate-400 hover:text-slate-600"
                            wire:click="clearFilter('search')">✕</button>
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ================= MOBILE FILTER SHEET ================= --}}
    <div class="fixed inset-0 z-40 bg-black/30 md:hidden" x-show="mobileFiltersOpen" x-transition.opacity x-cloak
        @click="mobileFiltersOpen = false"></div>

    <div class="fixed inset-y-0 right-0 z-50 w-full max-w-xs bg-white shadow-xl md:hidden flex flex-col"
        x-show="mobileFiltersOpen" x-transition:enter="transform transition ease-out duration-150"
        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in duration-150" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full" x-cloak @click.away="mobileFiltersOpen = false">
        <div class="flex items-center justify-between border-b border-slate-200 px-4 py-3">
            <h2 class="text-sm font-semibold text-slate-900">Filters</h2>
            <button type="button" class="rounded-full p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
                @click="mobileFiltersOpen = false">
                ✕
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-4 text-xs">
            <div>
                <label class="block text-[11px] font-medium text-slate-600">Date range</label>
                <div class="mt-1 flex gap-2">
                    <input type="date"
                        class="w-1/2 rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        wire:model.live="startDate">
                    <input type="date"
                        class="w-1/2 rounded-lg border border-slate-300 px-2 py-1.5 text-xs focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        wire:model.live="endDate">
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Quick ranges</label>
                <div class="grid grid-cols-4 gap-1">
                    <button type="button"
                        class="rounded-lg border px-2 py-1 text-[11px] font-medium
                                   {{ $range === 'today' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 text-slate-600' }}"
                        wire:click="setRange('today')">Today</button>
                    <button type="button"
                        class="rounded-lg border px-2 py-1 text-[11px] font-medium
                                   {{ $range === '7d' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 text-slate-600' }}"
                        wire:click="setRange('7d')">7d</button>
                    <button type="button"
                        class="rounded-lg border px-2 py-1 text-[11px] font-medium
                                   {{ $range === '30d' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 text-slate-600' }}"
                        wire:click="setRange('30d')">30d</button>
                    <button type="button"
                        class="rounded-lg border px-2 py-1 text-[11px] font-medium
                                   {{ $range === 'mtd' ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-200 text-slate-600' }}"
                        wire:click="setRange('mtd')">MTD</button>
                </div>
            </div>

            <div>
                <label class="block text-[11px] font-medium text-slate-600">Department</label>
                <select
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    wire:model.live="dept">
                    <option value="">-- All --</option>
                    @foreach ($departments as $d)
                        <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                    @endforeach
                </select>
            </div>

            @if ($user->specification->name === 'VERIFICATOR')
                <div>
                    <label class="block text-[11px] font-medium text-slate-600">Info</label>
                    <select
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        wire:model.live="infoStatus">
                        <option value="">-- Semua --</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-medium text-slate-600">Push by Verificator</label>
                    <select
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        wire:model.live="isPush">
                        <option value="">-- All --</option>
                        <option value="1">Already Pushed</option>
                        <option value="0">Not Yet Pushed</option>
                    </select>
                </div>
            @endif
        </div>

        <div class="border-t border-slate-200 p-3">
            <button type="button"
                class="flex w-full items-center justify-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50"
                wire:click="resetFilters" wire:loading.attr="disabled" @click="mobileFiltersOpen = false">
                ⟲ Clear all
            </button>
        </div>
    </div>

    @php
        function sortIcon($field, $current, $dir)
        {
            if ($current !== $field) {
                return '↕';
            }
            return $dir === 'asc' ? '↑' : '↓';
        }
        function ariaSort($field, $current, $dir)
        {
            if ($current !== $field) {
                return 'none';
            }
            return $dir === 'asc' ? 'ascending' : 'descending';
        }

        $compact = $dense ? 'text-xs' : 'text-sm';
        $rowPadding = $dense ? 'py-1.5' : 'py-2.5';
    @endphp

    {{-- ================= TABLE ================= --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto" wire:loading.class="opacity-50">

            {{-- Data table (not loading) --}}
            <table wire:loading.remove wire:key="table-data"
                class="min-w-full divide-y divide-slate-200 {{ $compact }}">
                <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th scope="col" class="whitespace-nowrap px-3 py-2 text-center cursor-pointer select-none"
                            wire:click="sortBy('id')" aria-sort="{{ ariaSort('id', $sortField, $sortDirection) }}">
                            <span class="inline-flex items-center gap-1">
                                ID <span class="text-[10px]">{{ sortIcon('id', $sortField, $sortDirection) }}</span>
                            </span>
                        </th>
                        <th scope="col" class="whitespace-nowrap px-3 py-2 text-center">Admin</th>
                        <th scope="col" class="whitespace-nowrap px-3 py-2 text-center">Dept</th>
                        <th scope="col" class="whitespace-nowrap px-3 py-2 text-center">Branch</th>
                        <th scope="col" class="whitespace-nowrap px-3 py-2 text-center cursor-pointer select-none"
                            wire:click="sortBy('first_overtime_date')"
                            aria-sort="{{ ariaSort('first_overtime_date', $sortField, $sortDirection) }}">
                            <span class="inline-flex items-center gap-1">
                                Overtime Date
                                <span class="text-[10px]">
                                    {{ sortIcon('first_overtime_date', $sortField, $sortDirection) }}
                                </span>
                            </span>
                        </th>
                        <th scope="col" class="whitespace-nowrap px-3 py-2 text-center cursor-pointer select-none"
                            wire:click="sortBy('status')"
                            aria-sort="{{ ariaSort('status', $sortField, $sortDirection) }}">
                            <span class="inline-flex items-center gap-1">
                                Status
                                <span class="text-[10px]">{{ sortIcon('status', $sortField, $sortDirection) }}</span>
                            </span>
                        </th>
                        <th scope="col" class="whitespace-nowrap px-3 py-2 text-center">Type</th>
                        <th scope="col" class="whitespace-nowrap px-3 py-2 text-center">Is After Hour?</th>
                        <th scope="col" class="whitespace-nowrap px-3 py-2 text-center">Info</th>
                        <th scope="col" class="whitespace-nowrap px-3 py-2 text-center">Action</th>
                        <th scope="col" class="whitespace-nowrap px-3 py-2 text-center">Created At</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-[11px] sm:text-xs">
                    @forelse ($dataheader as $fot)
                        <tr wire:key="row-{{ $fot->id }}"
                            class="{{ $fot->is_planned ? 'bg-white hover:bg-slate-50' : 'bg-rose-50 hover:bg-rose-100' }} transition-colors">
                            <td class="whitespace-nowrap px-3 {{ $rowPadding }} text-center text-slate-700">
                                {{ $fot->id }}
                            </td>
                            <td class="whitespace-nowrap px-3 {{ $rowPadding }} text-center text-slate-700">
                                {{ $fot->user->name }}
                            </td>
                            <td class="whitespace-nowrap px-3 {{ $rowPadding }} text-center">
                                <span
                                    class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[10px] font-medium text-slate-700">
                                    {{ $fot->department->name }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 {{ $rowPadding }} text-center text-slate-700">
                                {{ $fot->branch }}
                            </td>
                            <td class="whitespace-nowrap px-3 {{ $rowPadding }} text-center text-slate-700">
                                {{ $fot->first_overtime_date ? \Carbon\Carbon::parse($fot->first_overtime_date)->format('d-m-Y') : '-' }}
                            </td>
                            <td class="whitespace-nowrap px-3 {{ $rowPadding }} text-center">
                                <div class="flex flex-col items-center gap-1">
                                    <x-overtime-form-status-badge :status="$fot->status" />
                                    @if ($fot->is_push == 1)
                                        <div class="text-[10px] text-emerald-600" title="Pushed by Verificator">
                                            ✔ Finish by Bu Bernadett
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 {{ $rowPadding }} text-center">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold
                                       {{ $fot->is_planned ? 'border border-slate-300 bg-slate-50 text-slate-700' : 'bg-rose-600 text-white' }}"
                                    title="Planned if created before today; otherwise Urgent">
                                    {{ $fot->is_planned ? 'Planned' : 'Urgent' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 {{ $rowPadding }} text-center text-slate-700">
                                {{ $fot->is_after_hour ? 'Yes' : 'No' }}
                            </td>
                            <td class="px-3 {{ $rowPadding }} text-left align-top">
                                <div class="flex flex-col gap-1">
                                    @if ($fot->approved_count)
                                        <span
                                            class="inline-flex w-fit rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700">
                                            Approved: {{ $fot->approved_count }}
                                        </span>
                                    @endif
                                    @if ($fot->rejected_count)
                                        <span
                                            class="inline-flex w-fit rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-medium text-rose-700">
                                            Rejected: {{ $fot->rejected_count }}
                                        </span>
                                    @endif
                                    @if ($fot->pending_count)
                                        <span
                                            class="inline-flex w-fit rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-700">
                                            Pending: {{ $fot->pending_count }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 {{ $rowPadding }} text-center">
                                <div class="flex flex-wrap items-center justify-center gap-1.5">
                                    <a href="{{ route('overtime.detail', ['id' => $fot->id]) }}" target="_blank"
                                        class="inline-flex items-center gap-1 rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                                        <span>ℹ</span>
                                        <span>Detail</span>
                                    </a>
                                    <button type="button"
                                        class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1 text-[11px] font-medium text-rose-700 shadow-sm hover:bg-rose-100"
                                        wire:click="$dispatch('confirm-delete', { id: {{ $fot->id }} })"
                                        wire:loading.attr="disabled" title="Delete this record">
                                        <span>🗑</span>
                                        <span>Delete</span>
                                    </button>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 {{ $rowPadding }} text-center text-slate-700">
                                {{ \Carbon\Carbon::parse($fot->created_at)->timezone('Asia/Jakarta')->format('d-m-Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-3 py-10 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-3xl">📭</span>
                                    <p class="text-xs sm:text-sm">No data matches your filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Skeleton table (loading) --}}
            @php
                $cols = 11;
                $rows = min($perPage ?? 10, 10);
            @endphp
            <table wire:loading wire:key="table-skeleton"
                wire:target="resetFilters,setRange,sortBy,perPage,search,dept,startDate,endDate,infoStatus,isPush"
                class="min-w-full divide-y divide-slate-200 {{ $compact }}" aria-busy="true">
                <thead class="bg-slate-50 text-[11px] font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-3 py-2 text-center">ID</th>
                        <th class="px-3 py-2 text-center">Admin</th>
                        <th class="px-3 py-2 text-center">Dept</th>
                        <th class="px-3 py-2 text-center">Branch</th>
                        <th class="px-3 py-2 text-center">Overtime Date</th>
                        <th class="px-3 py-2 text-center">Status</th>
                        <th class="px-3 py-2 text-center">Type</th>
                        <th class="px-3 py-2 text-center">Is After Hour?</th>
                        <th class="px-3 py-2 text-center">Info</th>
                        <th class="px-3 py-2 text-center">Action</th>
                        <th class="px-3 py-2 text-center">Created At</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @for ($r = 0; $r < $rows; $r++)
                        <tr>
                            @for ($c = 0; $c < $cols; $c++)
                                <td class="px-3 py-2">
                                    <div class="h-3 rounded bg-slate-100 animate-pulse"></div>
                                </td>
                            @endfor
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $dataheader->links() }}
    </div>

    {{-- ================= DELETE CONFIRMATION MODAL ================= --}}
    <div x-cloak x-show="deleteOpen">
        {{-- Backdrop --}}
        <div class="fixed inset-0 z-40 bg-black/40" x-show="deleteOpen" x-transition.opacity></div>

        {{-- Modal --}}
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4" x-show="deleteOpen" x-transition
            role="dialog" aria-modal="true">
            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                    <h5 class="text-sm font-semibold text-slate-900">
                        Delete Form Overtime
                    </h5>
                    <button type="button"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        @click="deleteOpen = false">
                        <span class="sr-only">Close</span>
                        ✕
                    </button>
                </div>

                <div class="px-4 py-3 text-sm text-slate-600">
                    Are you sure you want to delete
                    <span class="font-semibold text-slate-900">#{{ $pendingDeleteId }}</span>?
                    <div class="mt-1 text-xs text-slate-400">
                        This action cannot be undone.
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 px-4 py-3">
                    <button type="button"
                        class="inline-flex items-center rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1"
                        @click="deleteOpen = false">
                        Cancel
                    </button>
                    <button type="button"
                        class="inline-flex items-center rounded-md bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-1"
                        wire:click="deleteConfirmed" wire:target="deleteConfirmed" wire:loading.attr="disabled">
                        <svg xmlns="http://www.w3.org/2000/svg" class="mr-1 h-3.5 w-3.5" viewBox="0 0 20 20"
                            fill="currentColor" wire:loading wire:target="deleteConfirmed">
                            <path fill-rule="evenodd"
                                d="M4 4a1 1 0 011-1h2.382a1 1 0 01.723.304l.89.892H15a1 1 0 011 1v1a1 1 0 11-2 0V6H6.618a1 1 0 01-.723-.304L5.006 5H5v10h1a1 1 0 110 2H5a2 2 0 01-2-2V4a1 1 0 011-1h0z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
