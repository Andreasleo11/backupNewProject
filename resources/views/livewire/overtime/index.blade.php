@section('title', 'Overtime Requests')
@section('page-title', $isPrivileged ? 'Overtime Overview' : 'My Overtime Requests')
@section('page-subtitle', $isPrivileged ? 'Monitor and manage overtime submissions across departments' : 'Track the status of your submitted overtime requests')

@php
    use App\Livewire\Overtime\Index as OvertimeIndex;

    // Stepper status → dot classes (JIT-safe full strings)
    $stepDot = [
        'approved' => 'bg-emerald-500 border-emerald-500',
        'rejected' => 'bg-rose-500 border-rose-500',
        'pending'  => 'bg-white border-slate-300',
    ];

    $compact    = 'text-[11px]';
    $rowPadding = 'py-2 px-4';

    function sortIcon($field, $current, $dir) {
        if ($current !== $field) return "<i class='bx bx-sort text-slate-300'></i>";
        return $dir === 'asc' ? "<i class='bx bx-sort-up text-indigo-600'></i>" : "<i class='bx bx-sort-down text-indigo-600'></i>";
    }
@endphp

<div class="space-y-6"
    x-data="{ deleteOpen: false, filtersOpen: false }"
    x-on:show-delete-modal.window="deleteOpen = true"
    x-on:hide-delete-modal.window="deleteOpen = false">

    {{-- ===== HEADER ===== --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-indigo-600 shadow-sm border border-slate-200/60">
                <i class='bx bx-time-five text-xl'></i>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-800 tracking-tight">
                    {{ $isPrivileged ? 'Overtime Overview' : 'My Overtime Requests' }}
                </h1>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    {{ $dataheader->total() }} {{ Str::plural('record', $dataheader->total()) }} match your current filters
                </p>
            </div>
        </div>

        @if (!$user->hasRole('MANAGEMENT'))
            <a href="{{ route('overtime.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-2.5 text-xs font-black text-white shadow-md shadow-emerald-500/20 transition-all hover:bg-emerald-700 focus:ring-2 focus:ring-emerald-500">
                <i class='bx bx-plus-circle text-lg'></i>
                New Overtime Request
            </a>
        @endif
    </div>

    {{-- ================================================================
         SECTION A: DETAIL-LEVEL REVIEW METRICS — Verificator / super-admin only.
         These counts (approved/rejected/pending) reflect individual OT detail
         rows that the Verificator approves/rejects AFTER the signing flow completes.
         They are meaningless to signers (GM, Director, Dept Head) who only
         interact with the form-level approval flow.
    ================================================================ --}}
    @if ($isDetailReviewer)
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Detail Review Status</span>
                    <p class="text-[10px] text-slate-400 mt-0.5">Per-employee rows across all visible forms</p>
                </div>
            </div>

            {{-- Skeleton --}}
            <div class="grid grid-cols-3 gap-4" wire:loading wire:target="startDate,endDate,dept,infoStatus,isPush,search,range,perPage">
                @for($i = 0; $i < 3; $i++)
                <div class="glass-card rounded-2xl border border-slate-100/60 p-5 animate-pulse">
                    <div class="flex items-center gap-4"><div class="h-12 w-12 rounded-xl bg-slate-100"></div><div class="flex-1 space-y-2"><div class="h-2 w-1/3 rounded bg-slate-100"></div><div class="h-5 w-1/4 rounded bg-slate-100"></div><div class="h-1.5 w-full rounded-full bg-slate-100"></div></div></div>
                </div>
                @endfor
            </div>

            {{-- Metric Cards --}}
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3" wire:loading.remove wire:target="startDate,endDate,dept,infoStatus,isPush,search,range,perPage">
                @foreach ([
                    ['key' => 'approved', 'label' => 'Approved',  'icon' => 'bx-check-circle',   'color' => 'emerald', 'base' => 'bg-emerald-100 text-emerald-600', 'hover' => 'group-hover:bg-emerald-500', 'bar' => 'bg-emerald-500', 'ring' => 'border-emerald-400 bg-emerald-50/40'],
                    ['key' => 'rejected', 'label' => 'Rejected',  'icon' => 'bx-x-circle',       'color' => 'rose',    'base' => 'bg-rose-100 text-rose-600',       'hover' => 'group-hover:bg-rose-500',    'bar' => 'bg-rose-500',    'ring' => 'border-rose-400 bg-rose-50/40'],
                    ['key' => 'pending',  'label' => 'Pending',   'icon' => 'bx-time-five',      'color' => 'amber',   'base' => 'bg-amber-100 text-amber-600',     'hover' => 'group-hover:bg-amber-500',   'bar' => 'bg-amber-500',   'ring' => 'border-amber-400 bg-amber-50/40'],
                ] as $card)
                <button type="button" wire:click="setInfoFilter('{{ $card['key'] }}')"
                    class="glass-card group flex w-full rounded-2xl p-5 text-left shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md focus:outline-none
                        {{ $infoStatus === $card['key'] ? 'border-2 '.$card['ring'] : 'border border-slate-100/60' }}">
                    <div class="flex w-full items-center gap-4">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $card['base'] }} {{ $card['hover'] }} group-hover:text-white transition-colors duration-300">
                            <i class="bx {{ $card['icon'] }} text-2xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $card['label'] }}</div>
                            <div class="mt-1 text-2xl font-black text-slate-800 tabular-nums">{{ number_format($stats[$card['key']]) }}</div>
                            <div class="mt-2.5 h-1 w-full overflow-hidden rounded-full bg-slate-100">
                                <div class="{{ $card['bar'] }} h-full transition-all duration-500" style="width: {{ $stats['pct_'.$card['key']] }}%"></div>
                            </div>
                        </div>
                    </div>
                </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ================================================================
         SECTION B: "Action Required" alert strip (dept-head / GM only)
         Surfaces pending signatures without needing to read the table.
    ================================================================ --}}
    @if ($isDetailReviewer && $stats['pending'] > 0 && $infoStatus !== 'pending')
        <div class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
            <i class='bx bx-bell-ring text-xl text-amber-600'></i>
            <div class="flex-1 text-xs font-bold text-amber-800">
                <strong>{{ $stats['pending'] }} {{ Str::plural('form', $stats['pending']) }}</strong> are waiting for approval action.
            </div>
            <button wire:click="setInfoFilter('pending')" class="rounded-lg bg-amber-500 px-3 py-1.5 text-[10px] font-black text-white hover:bg-amber-600 transition-colors">
                View Pending
            </button>
        </div>
    @endif

    {{-- ================================================================
         SECTION C: TOOLBAR + FILTERS
         Always-visible: Search + Quick Ranges + Per-page
         Collapsible:    Date range, Department, advanced filters
    ================================================================ --}}
    <div class="glass-card rounded-2xl border border-slate-100/60 shadow-sm">
        <div class="p-4 space-y-3">

            {{-- Top Row (always visible) --}}
            <div class="flex flex-wrap items-center gap-2">

                {{-- Search --}}
                <div class="relative w-full max-w-xs">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class='bx bx-search'></i>
                    </span>
                    <input type="text"
                        class="block w-full rounded-xl border-0 bg-slate-50 py-2 pl-9 pr-4 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-inset focus:ring-indigo-500"
                        placeholder="Search..." wire:model.live.debounce.400ms="search">
                </div>

                {{-- Quick Ranges (desktop) --}}
                <div class="hidden md:flex items-center gap-0.5 rounded-xl bg-slate-100 p-1 text-xs font-bold">
                    @foreach (['today' => 'Today', '7d' => '7d', '30d' => '30d', 'mtd' => 'MTD'] as $k => $v)
                        <button type="button" wire:click="setRange('{{ $k }}')"
                            class="rounded-lg px-3 py-1.5 transition-all {{ $range === $k ? 'bg-white text-indigo-700 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                            {{ $v }}
                        </button>
                    @endforeach
                </div>

                {{-- Right controls --}}
                <div class="ml-auto flex items-center gap-3">
                    

                    <select class="rounded-xl border-0 bg-slate-50 py-2 pl-3 pr-7 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200 focus:ring-indigo-500" wire:model.live="perPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>

                    {{-- Toggle advanced filters --}}
                    <button @click="filtersOpen = !filtersOpen"
                        class="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-xs font-bold transition-all
                            {{ ($startDate || $endDate || $dept || $infoStatus || ($isPush !== null && $isPush !== '')) ? 'bg-indigo-600 text-white shadow-md' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <i class='bx bx-slider-alt'></i>
                        <span class="hidden sm:inline">Filters</span>
                        @php 
                            $activeCount = (int)!!$startDate + (int)!!$endDate + (int)!!$dept + (int)!!$infoStatus + (int)($isPush !== null && $isPush !== ''); 
                        @endphp
                        @if ($activeCount > 0)
                            <span class="ml-0.5 inline-flex h-4 w-4 items-center justify-center rounded-full bg-white/30 text-[9px] font-black">{{ $activeCount }}</span>
                        @endif
                    </button>

                    {{-- Export: only for privileged --}}
                    @if ($isPrivileged)
                        <button type="button" wire:click="exportCsv" wire:loading.attr="disabled"
                            class="hidden sm:inline-flex items-center gap-1.5 rounded-xl bg-indigo-50 border border-indigo-100 px-3 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition-all">
                            <i class='bx bx-export'></i> Export
                        </button>
                    @endif
                </div>
            </div>

            {{-- Collapsible Advanced Filters --}}
            <div x-show="filtersOpen" x-collapse x-cloak class="border-t border-slate-100 pt-3">
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">From</label>
                        <input type="date" wire:model.live="startDate" class="rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">To</label>
                        <input type="date" wire:model.live="endDate" class="rounded-xl border-0 bg-slate-50 py-2 px-3 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200 focus:ring-indigo-500">
                    </div>

                    {{-- Department: only meaningful for privileged roles --}}
                    @if ($isPrivileged)
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Department</label>
                        <select wire:model.live="dept" class="rounded-xl border-0 bg-slate-50 py-2 pl-3 pr-8 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200 min-w-[160px] focus:ring-indigo-500">
                            <option value="">All Departments</option>
                            @foreach ($departments as $d)
                                <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    @if ($isDetailReviewer)
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Status</label>
                        <select wire:model.live="infoStatus" class="rounded-xl border-0 bg-slate-50 py-2 pl-3 pr-8 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200 focus:ring-indigo-500">
                            <option value="">All</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Payroll Push</label>
                        <select wire:model.live="isPush" class="rounded-xl border-0 bg-slate-50 py-2 pl-3 pr-8 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200 focus:ring-indigo-500">
                            <option value="">Any</option>
                            <option value="1">Pushed</option>
                            <option value="0">Not Pushed</option>
                        </select>
                    </div>
                    @endif

                    <div class="ml-auto">
                        <button wire:click="resetFilters" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-black text-slate-500 hover:bg-slate-50 transition-all shadow-sm">
                            <i class='bx bx-reset'></i> Clear All
                        </button>
                    </div>
                </div>
            </div>

            {{-- Active Filter Chips --}}
            @php $anyChip = $range || ($startDate && $endDate) || $dept || $infoStatus || $search; @endphp
            @if ($anyChip)
            <div class="flex flex-wrap gap-1.5 pt-1">
                @if ($range)       <button wire:click="clearFilter('range')"     class="inline-flex items-center gap-1 rounded-full bg-indigo-50 border border-indigo-100 px-2.5 py-1 text-[10px] font-black text-indigo-700 hover:bg-indigo-100">Range: {{ strtoupper($range) }} <i class='bx bx-x'></i></button> @endif
                @if ($startDate && $endDate) <button wire:click="clearFilter('dates')" class="inline-flex items-center gap-1 rounded-full bg-indigo-50 border border-indigo-100 px-2.5 py-1 text-[10px] font-black text-indigo-700 hover:bg-indigo-100">{{ date('d M y', strtotime($startDate)) }} – {{ date('d M y', strtotime($endDate)) }} <i class='bx bx-x'></i></button> @endif
                @if ($dept)        <button wire:click="clearFilter('dept')"      class="inline-flex items-center gap-1 rounded-full bg-indigo-50 border border-indigo-100 px-2.5 py-1 text-[10px] font-black text-indigo-700 hover:bg-indigo-100">{{ collect($departments)->firstWhere('id', $dept)['name'] ?? 'Dept' }} <i class='bx bx-x'></i></button> @endif
                @if ($infoStatus)  <button wire:click="clearFilter('infoStatus')"class="inline-flex items-center gap-1 rounded-full bg-indigo-50 border border-indigo-100 px-2.5 py-1 text-[10px] font-black text-indigo-700 hover:bg-indigo-100">{{ ucfirst($infoStatus) }} <i class='bx bx-x'></i></button> @endif
                @if ($search)      <button wire:click="clearFilter('search')"    class="inline-flex items-center gap-1 rounded-full bg-indigo-50 border border-indigo-100 px-2.5 py-1 text-[10px] font-black text-indigo-700 hover:bg-indigo-100">"{{ $search }}" <i class='bx bx-x'></i></button> @endif
            </div>
            @endif
        </div>
    </div>

    {{-- ================================================================
         SECTION D: TABLE + EMPTY STATE
    ================================================================ --}}
    @if ($dataheader->total() === 0 && !$anyChip ?? false)
        {{-- ===== ZERO-RECORD EMPTY STATE (New User Onboarding) ===== --}}
        <div class="glass-card rounded-2xl border border-slate-100/60 shadow-sm py-20 px-6 text-center">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-emerald-50 border border-slate-100 shadow-inner mb-5">
                <i class='bx bx-time text-5xl text-indigo-300'></i>
            </div>
            <h3 class="text-lg font-black text-slate-700 tracking-tight">No overtime requests yet</h3>
            <p class="mt-2 text-sm font-medium text-slate-400 max-w-xs mx-auto leading-relaxed">
                Submit your first overtime request to get started. It only takes a minute.
            </p>
            @if (Auth::user()->department?->name !== 'MANAGEMENT')
                <a href="{{ route('overtime.create') }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all">
                    <i class='bx bx-plus-circle text-lg'></i> Create My First Request
                </a>
            @endif
        </div>
    @else
        {{-- ===== DATA TABLE ===== --}}
        <div class="glass-card overflow-hidden rounded-2xl border border-slate-100/60 shadow-sm">
            <div class="overflow-x-auto" wire:loading.class="opacity-60 pointer-events-none">

                {{-- Actual data table --}}
                <table class="min-w-full text-left align-middle" wire:loading.remove wire:key="tbl-data">
                    <thead class="border-b border-slate-200/60 bg-slate-50/80 text-[10px] font-black uppercase tracking-widest text-slate-500">
                        <tr>
                            <th wire:click="sortBy('id')" class="{{ $rowPadding }} cursor-pointer whitespace-nowrap hover:bg-slate-100/70 transition-colors">
                                <div class="flex items-center gap-1"># {!! sortIcon('id', $sortField, $sortDirection) !!}</div>
                            </th>
                            <th class="{{ $rowPadding }} whitespace-nowrap">Submitted By</th>
                            @if ($isPrivileged)
                                <th class="{{ $rowPadding }} whitespace-nowrap">Dept / Branch</th>
                            @endif
                            <th wire:click="sortBy('first_overtime_date')" class="{{ $rowPadding }} cursor-pointer whitespace-nowrap hover:bg-slate-100/70 transition-colors">
                                <div class="flex items-center gap-1">OT Date {!! sortIcon('first_overtime_date', $sortField, $sortDirection) !!}</div>
                            </th>
                            <th wire:click="sortBy('workflow_status')" class="{{ $rowPadding }} cursor-pointer whitespace-nowrap hover:bg-slate-100/70 transition-colors">
                                <div class="flex items-center gap-1">Status {!! sortIcon('workflow_status', $sortField, $sortDirection) !!}</div>
                            </th>
                            <th class="{{ $rowPadding }} whitespace-nowrap">Review Status</th>
                            <th class="{{ $rowPadding }} whitespace-nowrap">Approval</th>
                            <th class="{{ $rowPadding }} whitespace-nowrap text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100/60 bg-white/30 {{ $compact }}">
                        @forelse ($dataheader as $fot)
                            @php
                                $meta  = OvertimeIndex::statusMeta($fot->workflow_status);
                                $steps = $fot->approvalRequest?->steps ?? collect();
                            @endphp
                            <tr wire:key="row-{{ $fot->id }}" class="group hover:bg-indigo-50/30 transition-colors">

                                {{-- # / Plan badge --}}
                                <td class="{{ $rowPadding }} whitespace-nowrap">
                                    <div class="font-black text-slate-800 tabular-nums">#{{ $fot->id }}</div>
                                    <span class="mt-1 inline-flex items-center gap-0.5 rounded border px-1.5 py-0.5 text-[9px] font-black uppercase
                                        {{ $fot->is_planned ? 'bg-indigo-50 border-indigo-100 text-indigo-600' : 'bg-rose-50 border-rose-100 text-rose-600' }}">
                                        <i class='bx {{ $fot->is_planned ? 'bx-calendar-check' : 'bx-alarm-exclamation' }}'></i>
                                        {{ $fot->is_planned ? 'Planned' : 'Urgent' }}
                                    </span>
                                </td>

                                {{-- Submitted By --}}
                                <td class="{{ $rowPadding }} whitespace-nowrap">
                                    <div class="font-bold text-slate-700">{{ $fot->user?->name ?? 'Unknown' }}</div>
                                    <div class="text-[9px] text-slate-400 mt-0.5 tabular-nums">{{ $fot->created_at?->format('d M y · H:i') }}</div>
                                </td>

                                {{-- Dept/Branch: privileged only --}}
                                @if ($isPrivileged)
                                <td class="{{ $rowPadding }} whitespace-nowrap">
                                    <span class="inline-flex rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] font-black text-slate-600">
                                        {{ $fot->department?->name ?? '-' }}
                                    </span>
                                    <div class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">{{ $fot->branch }}</div>
                                </td>
                                @endif

                                {{-- OT Date --}}
                                <td class="{{ $rowPadding }} whitespace-nowrap font-bold text-slate-700">
                                    {{ $fot->first_overtime_date ? date('D, d M Y', strtotime($fot->first_overtime_date)) : '—' }}
                                </td>

                                {{-- Status Badge (JIT-safe via statusMeta) --}}
                                <td class="{{ $rowPadding }} whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-wide {{ $meta['classes'] }}">
                                        <i class="bx {{ $meta['icon'] }}"></i>{{ $meta['label'] }}
                                    </span>
                                </td>

                                {{-- Review Status Badge --}}
                                @php $review = OvertimeIndex::reviewMeta($fot); @endphp
                                <td class="{{ $rowPadding }} whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-1.5">
                                            <span class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-[10px] font-black uppercase tracking-wide {{ $review['classes'] }}"
                                                @if(isset($review['reason'])) title="{{ $review['reason'] }}" @endif>
                                                <i class="bx {{ $review['icon'] }}"></i>{{ $review['label'] }}
                                            </span>
                                        </div>
                                        
                                        {{-- Detail Counts Summary --}}
                                        <div class="mt-1 flex items-center gap-1.5 px-1">
                                            <div class="text-[9px] font-black uppercase tracking-wider text-slate-400 whitespace-nowrap">
                                                <span class="{{ $fot->approved_count > 0 ? 'text-emerald-600' : '' }}">{{ $fot->approved_count }}</span>
                                                <span class="mx-0.5 text-slate-300">/</span>
                                                <span class="text-slate-700">{{ $fot->details_count }}</span>
                                                <span class="ml-1 opacity-60">Rows</span>
                                            </div>
                                            @if($fot->rejected_count > 0)
                                                <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                                                <div class="text-[9px] font-black uppercase tracking-wider text-rose-500">
                                                    {{ $fot->rejected_count }} Rejected
                                                </div>
                                            @endif
                                        </div>

                                        @if(isset($review['reason']))
                                            <div class="mt-1 text-[9px] text-slate-400 max-w-[150px] truncate font-medium italic" title="{{ $review['reason'] }}">
                                                {{ $review['reason'] }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Inline Approval Stepper --}}
                                <td class="{{ $rowPadding }} whitespace-nowrap">
                                    @can('viewTimeline', $fot)
                                        @if ($steps->isEmpty())
                                            <span class="text-[10px] text-slate-400 italic">Not submitted</span>
                                        @else
                                            <div class="flex items-center gap-1">
                                                @foreach ($steps as $step)
                                                    @php
                                                        // Engine stores UPPERCASE: APPROVED / REJECTED / PENDING
                                                        $dotStatus = match(strtolower($step->status ?? '')) {
                                                            'approved'            => 'approved',
                                                            'rejected', 'canceled'=> 'rejected',
                                                            default               => 'pending',
                                                        };
                                                    @endphp
                                                    {{-- Connector line (not before first dot) --}}
                                                    @if (!$loop->first)
                                                        <div class="h-px w-3 {{ $dotStatus === 'approved' ? 'bg-emerald-400' : 'bg-slate-200' }}"></div>
                                                    @endif

                                                    {{-- Dot with tooltip --}}
                                                    <div title="{{ $step->approver_snapshot_label ?? 'Step '.$step->sequence }}"
                                                        class="relative h-4 w-4 rounded-full border-2 flex items-center justify-center cursor-default transition-all
                                                            {{ $stepDot[$dotStatus] }}">
                                                        @if ($dotStatus === 'approved')
                                                            <svg class="h-2 w-2 text-white" fill="none" viewBox="0 0 8 8"><path stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M1 4l2 2 4-4"/></svg>
                                                        @elseif($dotStatus === 'rejected')
                                                            <svg class="h-2 w-2 text-white" fill="none" viewBox="0 0 8 8"><path stroke="currentColor" stroke-width="1.5" stroke-linecap="round" d="M2 2l4 4M6 2L2 6"/></svg>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mt-1 text-[9px] text-slate-400 tabular-nums">
                                                {{ $steps->filter(fn($s) => strtolower($s->status ?? '') === 'approved')->count() }}/{{ $steps->count() }} signed
                                            </div>
                                        @endif
                                    @else
                                        <div class="flex items-center gap-1 opacity-20 filter grayscale">
                                            @for($i=0; $i<3; $i++)
                                                <div class="h-3 w-3 rounded-full bg-slate-200"></div>
                                            @endfor
                                        </div>
                                    @endcan
                                </td>

                                {{-- Action: only the Detail link on the list. Delete lives in the detail page. --}}
                                <td class="{{ $rowPadding }} whitespace-nowrap text-right">
                                    <div class="flex items-center justify-end gap-1.5 opacity-60 group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('overtime.detail', $fot->id) }}"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 border border-indigo-100 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors shadow-sm"
                                            title="View Details">
                                            <i class='bx bx-right-arrow-alt text-lg'></i>
                                        </a>
                                        {{-- Delete: only shown to authorized users --}}
                                        @can('delete', $fot)
                                        <button wire:click="$dispatch('confirm-delete', { id: {{ $fot->id }} })"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 border border-rose-100 text-rose-500 hover:bg-rose-600 hover:text-white transition-colors shadow-sm"
                                            title="Delete">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            {{-- Filtered-empty state (filters active, 0 results) --}}
                            <tr>
                                <td colspan="{{ $isPrivileged ? 7 : 6 }}" class="px-6 py-16 text-center">
                                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-300 border border-slate-100 mb-4">
                                        <i class='bx bx-filter-alt text-3xl'></i>
                                    </div>
                                    <h3 class="text-sm font-black text-slate-700">No results match your filters</h3>
                                    <p class="text-xs text-slate-400 mt-1">Try adjusting your date range or removing some filters.</p>
                                    <button wire:click="resetFilters" class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-4 py-2 text-xs font-black text-slate-600 hover:bg-slate-200 transition-all">
                                        <i class='bx bx-reset'></i> Clear All Filters
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- Skeleton Loader --}}
                @php $cols = $isPrivileged ? 7 : 6; @endphp
                <table class="min-w-full" wire:loading wire:key="tbl-skeleton" wire:target="resetFilters,setRange,sortBy,perPage,search,dept,startDate,endDate,infoStatus,isPush">
                    <tbody class="divide-y divide-slate-100/60">
                        @for ($i = 0; $i < min(8, $perPage); $i++)
                        <tr>
                            @for ($j = 0; $j < $cols; $j++)
                                <td class="{{ $rowPadding }} animate-pulse"><div class="h-4 rounded bg-slate-100"></div></td>
                            @endfor
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Pagination --}}
    @if ($dataheader->hasPages())
        <div class="pb-8">{{ $dataheader->links() }}</div>
    @endif

    {{-- ================================================================
         DELETE CONFIRMATION MODAL
    ================================================================ --}}
    <template x-teleport="body">
        <div x-cloak x-show="deleteOpen" class="relative z-[60]">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" x-show="deleteOpen" x-transition.opacity @click="deleteOpen = false"></div>
            <div class="fixed inset-0 z-[70] flex items-center justify-center p-4" x-show="deleteOpen" x-transition role="dialog" aria-modal="true">
                <div class="w-full max-w-sm rounded-3xl bg-white/95 backdrop-blur-2xl shadow-2xl border border-white/80 p-6 text-center relative overflow-hidden" @click.stop>
                    <div class="absolute -top-10 -right-10 h-28 w-28 rounded-full bg-rose-50 blur-2xl pointer-events-none"></div>
                    <div class="absolute -bottom-10 -left-10 h-28 w-28 rounded-full bg-rose-50 blur-2xl pointer-events-none"></div>
                    <div class="relative z-10">
                        <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                            <i class='bx bx-trash text-3xl'></i>
                        </div>
                        <h3 class="text-base font-black text-slate-800">Delete OT-{{ $pendingDeleteId }}?</h3>
                        <p class="mt-2 text-xs text-slate-500 leading-relaxed mb-5">
                            This will permanently remove all detail rows and approval data for this form. This action cannot be undone.
                        </p>
                        <div class="flex gap-2">
                            <button @click="deleteOpen = false" class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-xs font-black text-slate-600 hover:bg-slate-50 transition-all">Cancel</button>
                            <button wire:click="deleteConfirmed" wire:loading.attr="disabled"
                                class="flex-1 rounded-xl bg-rose-600 py-2.5 text-xs font-black text-white shadow-md shadow-rose-500/20 hover:bg-rose-700 disabled:opacity-50 transition-all">
                                <i class='bx bx-trash'></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

</div>
