@section('title', 'Overtime Requests')
@section('page-title', $isPrivileged ? 'Overtime Overview' : 'My Overtime Requests')
@section('page-subtitle', $isPrivileged ? 'Monitor and manage overtime submissions across departments' : 'Track the
    status of your submitted overtime requests')
    @php
        use App\Application\Overtime\Presenters\OvertimePresenter;
        use App\Livewire\Overtime\Index as OvertimeIndex;

        // Stepper status → dot classes (JIT-safe full strings)
        $stepDot = [
            'approved' => 'bg-emerald-500 border-emerald-500',
            'rejected' => 'bg-rose-500 border-rose-500',
            'pending' => 'bg-white border-slate-300',
        ];

        $compact = 'text-[11px]';
        $rowPadding = 'py-2 px-4';

        if (!function_exists('sortIcon')) {
            function sortIcon($field, $current, $dir)
            {
                if ($current !== $field) {
                    return "<i class='bx bx-sort text-slate-300'></i>";
                }
                return $dir === 'asc'
                    ? "<i class='bx bx-sort-up text-indigo-600'></i>"
                    : "<i class='bx bx-sort-down text-indigo-600'></i>";
            }
        }
    @endphp

    <div class="space-y-6" x-data="{
        deleteOpen: false,
        filtersOpen: false,
        selectedIds: @entangle('selectedIds'),
        get isAllSelected() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            if (checkboxes.length === 0) return false;
            return checkboxes.length === document.querySelectorAll('.row-checkbox:checked').length;
        },
        toggleAll(e) {
            if (e.target.checked) {
                this.selectedIds = Array.from(document.querySelectorAll('.row-checkbox')).map(cb => cb.value);
            } else {
                this.selectedIds = [];
            }
        }
    }" x-on:show-delete-modal.window="deleteOpen = true"
        x-on:hide-delete-modal.window="deleteOpen = false">

        {{-- ===== HEADER ===== --}}
        <div class="relative overflow-hidden rounded-3xl bg-white border border-slate-200/60 p-6 shadow-sm">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div
                        class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white shadow-lg shadow-indigo-200">
                        <i class='bx bx-time-five text-3xl'></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-slate-800 tracking-tight">
                            {{ $isPrivileged ? 'Overtime Command Center' : 'My Overtime Portal' }}
                        </h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-slate-500 border border-slate-200">
                                <i class='bx bx-data'></i> {{ $dataheader->total() }} Records
                            </span>
                            @if ($infoStatus)
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-indigo-600 border border-indigo-100">
                                    <i class='bx bx-filter-alt'></i> {{ ucfirst($infoStatus) }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    @if ($isDetailReviewer || $isPrivileged)
                        <a href="{{ route('overtime.hub') }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-5 py-2.5 text-xs font-black text-slate-700 shadow-sm transition-all hover:bg-slate-50 hover:border-slate-300 focus:ring-2 focus:ring-slate-200 group">
                            <i class='bx bxs-zap text-lg text-indigo-500 group-hover:scale-110 transition-transform'></i>
                            Switch to Hub
                        </a>
                    @endif

                    <a href="{{ route('overtime.bulk') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-5 py-2.5 text-xs font-black text-indigo-600 shadow-sm transition-all hover:bg-indigo-50 hover:border-indigo-200 focus:ring-2 focus:ring-slate-200">
                        <i class='bx bx-cloud-upload text-lg'></i>
                        Smart Bulk
                    </a>

                    <a href="{{ route('overtime.create') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-2.5 text-xs font-black text-white shadow-md shadow-indigo-500/20 transition-all hover:bg-slate-900 shadow-sm active:translate-y-0 focus:ring-2 focus:ring-indigo-500">
                        <i class='bx bx-plus-circle text-lg'></i>
                        New Request
                    </a>
                </div>
            </div>

            {{-- Subtle background decoration --}}
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-indigo-50/50 blur-3xl pointer-events-none">
            </div>
        </div>

        {{-- ================================================================
         SECTION A: DETAIL-LEVEL REVIEW METRICS — Verificator / super-admin only.
         These counts (approved/rejected/pending) reflect individual OT detail
         rows that the Verificator approves/rejects AFTER the signing flow completes.
         They are meaningless to signers (GM, Director, Dept Head) who only
         interact with the form-level approval flow.
    ================================================================ --}}
        {{-- ===== SMART METRICS (INSIGHTS) ===== --}}
        @if ($isDetailReviewer)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 transition-all duration-300"
                wire:loading.class="opacity-50 saturate-50 pointer-events-none"
                wire:target="startDate,endDate,dept,infoStatus,search,range,perPage,clearFilter">
                @foreach ([['key' => 'pending', 'label' => 'Awaiting Review', 'icon' => 'bx-time-five', 'color' => 'amber', 'bg' => 'bg-amber-500'], ['key' => 'approved', 'label' => 'Approved', 'icon' => 'bx-check-double', 'color' => 'emerald', 'bg' => 'bg-emerald-500'], ['key' => 'rejected', 'label' => 'Rejected', 'icon' => 'bx-x-circle', 'color' => 'rose', 'bg' => 'bg-rose-500']] as $card)
                    <button type="button" wire:click="setInfoFilter('{{ $card['key'] }}')"
                        class="group relative overflow-hidden rounded-2xl bg-white border border-slate-200/60 p-4 text-left transition-all hover:shadow-md hover:-translate-y-0.5
                    {{ $infoStatus === $card['key'] ? 'ring-2 ring-' . $card['color'] . '-500 border-transparent shadow-md' : '' }}">
                        <div class="flex items-center gap-4">
                            <div
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-50 text-slate-400 group-hover:bg-{{ $card['color'] }}-50 group-hover:text-{{ $card['color'] }}-600 transition-colors">
                                <i class="bx {{ $card['icon'] }} text-xl"></i>
                            </div>
                            <div>
                                <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                                    {{ $card['label'] }}</div>
                                <div class="text-xl font-black text-slate-800 tracking-tight">
                                    {{ number_format($stats[$card['key']]) }}</div>
                            </div>
                        </div>
                        {{-- Progress mini-bar --}}
                        <div class="absolute bottom-0 left-0 h-1 bg-slate-100 w-full overflow-hidden">
                            <?php $pctValue = $stats['pct_' . $card['key']] ?? 0; ?>
                            <div class="<?php echo $card['bg']; ?> h-full transition-all duration-700"
                                style="width: <?php echo $pctValue; ?>%"></div>
                        </div>
                    </button>
                @endforeach

                {{-- Total Summary / Efficiency Card --}}
                <div class="rounded-2xl bg-slate-800 p-4 text-white shadow-lg shadow-slate-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Density</div>
                            <div class="text-xl font-black text-white tracking-tight">{{ number_format($stats['total']) }}
                                <span class="text-[10px] opacity-60">Rows</span></div>
                        </div>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10">
                            <i class='bx bx-pie-chart-alt-2 text-xl'></i>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ================================================================
         SECTION B: ACTION REQUIRED SURFACING
    ================================================================ --}}

        {{-- 1. Signature Action Strip (For ANY approver) --}}
        @if ($stats['my_approval_count'] > 0 && $infoStatus !== 'my_approval')
            <div
                class="flex items-center gap-3 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-3 shadow-md shadow-indigo-100/50">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                    <i class='bx bx-edit text-xl'></i>
                </div>
                <div class="flex-1 text-sm font-bold text-indigo-900 tracking-tight">
                    You have <strong>{{ $stats['my_approval_count'] }}</strong> overtime
                    {{ Str::plural('request', $stats['my_approval_count']) }} requiring your signature.
                </div>
                <button wire:click="setInfoFilter('my_approval')"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-black text-white hover:bg-indigo-700 hover:-translate-y-0.5 transition-all shadow-md shadow-indigo-500/20 active:translate-y-0">
                    Review Now
                </button>
            </div>
        @endif

        {{-- 2. Detail Review Action Strip (Verificator only) --}}
        @if ($isDetailReviewer && $stats['pending'] > 0 && $infoStatus !== 'pending')
            <div class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 mt-2">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                    <i class='bx bx-bell-ring text-xl'></i>
                </div>
                <div class="flex-1 text-xs font-bold text-amber-800">
                    <strong>{{ $stats['pending'] }} {{ Str::plural('form', $stats['pending']) }}</strong> are awaiting
                    detail verification.
                </div>
                <button wire:click="setInfoFilter('pending')"
                    class="rounded-lg bg-amber-500 px-3 py-1.5 text-[10px] font-black text-white hover:bg-amber-600 transition-colors">
                    Verify Now
                </button>
            </div>
        @endif

        {{-- ================================================================
         SECTION C: TOOLBAR + FILTERS
         Always-visible: Search + Quick Ranges + Per-page
         Collapsible:    Date range, Department, advanced filters
    ================================================================ --}}
        {{-- ===== COMMAND BAR ===== --}}
        <div class="rounded-2xl bg-white border border-slate-200/60 p-2 shadow-sm">
            <div class="flex flex-wrap items-center gap-2">

                {{-- Search & Main Filters --}}
                <div class="relative flex-1 min-w-[240px]">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class='bx bx-search text-lg'></i>
                    </span>
                    <input type="text"
                        class="block w-full rounded-xl border-0 bg-slate-50 py-2.5 pl-10 pr-4 text-sm text-slate-900 ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-indigo-600 transition-all placeholder:text-slate-400"
                        placeholder="Search by Employee, NIK, or ID..." wire:model.live.debounce.400ms="search">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" wire:loading
                        wire:target="search,startDate,endDate,dept,infoStatus,clearFilter,resetFilters,setRange">
                        <i class='bx bx-loader-alt animate-spin text-indigo-500 text-lg'></i>
                    </div>
                </div>

                {{-- Quick Stats (Mobile hidden) --}}
                <div class="hidden lg:flex items-center gap-1 rounded-xl bg-slate-50 border border-slate-200 p-1">
                    @foreach (['today' => 'Today', '7d' => '7d', '30d' => '30d', 'mtd' => 'MTD'] as $k => $v)
                        <button type="button" wire:click="setRange('{{ $k }}')"
                            class="rounded-lg px-3 py-1.5 text-xs font-black transition-all {{ $range === $k ? 'bg-white text-indigo-600 shadow-sm border border-slate-100' : 'text-slate-500 hover:text-slate-800' }}">
                            {{ $v }}
                        </button>
                    @endforeach
                </div>

                <div class="flex items-center gap-2 px-1">
                    <button @click="filtersOpen = !filtersOpen"
                        class="inline-flex h-10 items-center gap-2 rounded-xl px-4 text-xs font-black transition-all border
                        {{ $startDate || $endDate || $dept || $infoStatus ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-100' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50' }}">
                        <i class='bx bx-slider-alt text-base'></i>
                        <span>Filters</span>
                        @php
                            $activeCount = (int) !!$startDate + (int) !!$endDate + (int) !!$dept + (int) !!$infoStatus;
                        @endphp
                        @if ($activeCount > 0)
                            <span
                                class="ml-1 flex h-5 w-5 items-center justify-center rounded-full bg-white/20 text-[10px] font-black">{{ $activeCount }}</span>
                        @endif
                    </button>

                    {{-- Action Menu --}}
                    <div class="h-8 w-px bg-slate-200 mx-1"></div>

                    <select
                        class="h-10 rounded-xl border-slate-200 bg-white text-xs font-black text-slate-700 focus:ring-indigo-500"
                        wire:model.live="perPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>

                    @if ($isPrivileged)
                        <button type="button" wire:click="exportCsv" wire:loading.attr="disabled"
                            class="h-10 w-10 flex items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 hover:text-indigo-600 hover:border-indigo-100 hover:bg-indigo-50 transition-all"
                            title="Export CSV">
                            <i class='bx bx-export text-xl'></i>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Expanded Filters --}}
            <div x-show="filtersOpen" x-collapse x-cloak class="border-t border-slate-100 mt-2 pt-4 px-2 pb-2">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    {{-- Date Range --}}
                    <div class="space-y-2">
                        <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Time Period</label>
                        <div class="flex items-center gap-2">
                            <input type="date" wire:model.live="startDate"
                                class="flex-1 rounded-xl border-slate-200 bg-slate-50 py-2.5 px-3 text-xs font-bold text-slate-700 focus:ring-indigo-500">
                            <span class="text-slate-300 text-xs font-black">TO</span>
                            <input type="date" wire:model.live="endDate"
                                class="flex-1 rounded-xl border-slate-200 bg-slate-50 py-2.5 px-3 text-xs font-bold text-slate-700 focus:ring-indigo-500">
                        </div>
                    </div>

                    {{-- Department --}}
                    @if ($isPrivileged)
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Source
                                Entity</label>
                            <select wire:model.live="dept"
                                class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 px-3 text-xs font-bold text-slate-700 focus:ring-indigo-500">
                                <option value="">All Departments</option>
                                @foreach ($departments as $d)
                                    <option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if ($isDetailReviewer)
                        <div class="space-y-2">
                            <label class="text-[10px] font-black uppercase tracking-widest text-slate-400">Review
                                State</label>
                            <select wire:model.live="infoStatus"
                                class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 px-3 text-xs font-bold text-slate-700 focus:ring-indigo-500">
                                <option value="">All Statuses</option>
                                <option value="my_approval">Awaiting My Signature</option>
                                <option value="pending">Awaiting Audit</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end mt-4 pt-4 border-t border-slate-50">
                    <button wire:click="resetFilters"
                        class="text-xs font-black text-slate-400 hover:text-rose-500 transition-colors uppercase tracking-widest">
                        <i class='bx bx-refresh text-sm'></i> Reset All Filters
                    </button>
                </div>
            </div>

            {{-- Active Chips --}}
            @php $anyChip = $range || ($startDate && $endDate) || $dept || $infoStatus || $search; @endphp
            @if ($anyChip)
                <div class="flex flex-wrap gap-2 px-2 pb-2">
                    @if ($range)
                        <button wire:click="clearFilter('range')"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-2 py-1 text-[10px] font-black text-indigo-700 border border-indigo-100 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all">RANGE:
                            {{ strtoupper($range) }} <i class='bx bx-x text-sm'></i></button>
                    @endif
                    @if ($startDate && $endDate)
                        <button wire:click="clearFilter('dates')"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-2 py-1 text-[10px] font-black text-indigo-700 border border-indigo-100 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all">{{ date('d M y', strtotime($startDate)) }}
                            – {{ date('d M y', strtotime($endDate)) }} <i class='bx bx-x text-sm'></i></button>
                    @endif
                    @if ($dept)
                        <button wire:click="clearFilter('dept')"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-2 py-1 text-[10px] font-black text-indigo-700 border border-indigo-100 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all">{{ collect($departments)->firstWhere('id', $dept)['name'] ?? 'Dept' }}
                            <i class='bx bx-x text-sm'></i></button>
                    @endif
                    @if ($infoStatus)
                        <button
                            wire:click="clearFilter('infoStatus')"class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-2 py-1 text-[10px] font-black text-indigo-700 border border-indigo-100 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all">{{ $infoStatus === 'my_approval' ? 'MY APPROVAL' : strtoupper($infoStatus) }}
                            <i class='bx bx-x text-sm'></i></button>
                    @endif
                    @if ($search)
                        <button wire:click="clearFilter('search')"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-2 py-1 text-[10px] font-black text-indigo-700 border border-indigo-100 hover:bg-rose-50 hover:text-rose-600 hover:border-rose-100 transition-all">"{{ $search }}"
                            <i class='bx bx-x text-sm'></i></button>
                    @endif
                </div>
            @endif
        </div>

        {{-- ================================================================
         SECTION D: TABLE + EMPTY STATE
    ================================================================ --}}
        @if ($dataheader->total() === 0 && !$anyChip ?? false)
            {{-- ===== ZERO-RECORD EMPTY STATE (New User Onboarding) ===== --}}
            <div class="glass-card rounded-2xl border border-slate-100/60 shadow-sm py-20 px-6 text-center">
                <div
                    class="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-50 to-emerald-50 border border-slate-100 shadow-inner mb-5">
                    <i class='bx bx-time text-5xl text-indigo-300'></i>
                </div>
                <h3 class="text-lg font-black text-slate-700 tracking-tight">No overtime requests yet</h3>
                <p class="mt-2 text-sm font-medium text-slate-400 max-w-xs mx-auto leading-relaxed">
                    Submit your first overtime request to get started. It only takes a minute.
                </p>
                @if (Auth::user()->department?->name !== 'MANAGEMENT')
                    <a href="{{ route('overtime.create') }}"
                        class="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-indigo-500/20 hover:bg-indigo-700 transition-all">
                        <i class='bx bx-plus-circle text-lg'></i> Create My First Request
                    </a>
                @endif
            </div>
        @else
            {{-- ===== DATA TABLE ===== --}}
            <div class="glass-card overflow-hidden rounded-2xl border border-slate-100/60 shadow-sm">
                <div class="overflow-x-auto" wire:loading.class="opacity-60 pointer-events-none">

                    {{-- Actual data table --}}
                    <table class="min-w-full text-left align-middle" wire:loading.remove wire:key="tbl-data"
                        wire:target="resetFilters,setRange,sortBy,perPage,search,dept,startDate,endDate,infoStatus,clearFilter,gotoPage,nextPage,previousPage">
                        <thead
                            class="border-b border-slate-200/60 bg-slate-50/80 text-[10px] font-black uppercase tracking-widest text-slate-500">
                            <tr>
                                @if ($canApprove)
                                    <th class="w-12 px-6 py-4 bg-slate-50">
                                        <input type="checkbox" :checked="isAllSelected" @change="toggleAll"
                                            class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                    </th>
                                @endif
                                <th wire:click="sortBy('id')"
                                    class="{{ $rowPadding }} cursor-pointer whitespace-nowrap hover:bg-slate-100 transition-colors">
                                    <div class="flex items-center gap-1.5">REF {!! sortIcon('id', $sortField, $sortDirection) !!}</div>
                                </th>
                                <th class="{{ $rowPadding }} whitespace-nowrap text-slate-400">Creator</th>
                                <th class="{{ $rowPadding }} whitespace-nowrap">Dept / Branch</th>
                                <th wire:click="sortBy('first_overtime_date')"
                                    class="{{ $rowPadding }} cursor-pointer whitespace-nowrap hover:bg-slate-100 transition-colors">
                                    <div class="flex items-center gap-1.5">OT Date {!! sortIcon('first_overtime_date', $sortField, $sortDirection) !!}</div>
                                </th>
                                <th wire:click="sortBy('workflow_status')"
                                    class="{{ $rowPadding }} cursor-pointer whitespace-nowrap hover:bg-slate-100 transition-colors">
                                    <div class="flex items-center gap-1.5">Status {!! sortIcon('workflow_status', $sortField, $sortDirection) !!}</div>
                                </th>
                                <th class="{{ $rowPadding }} whitespace-nowrap text-right text-slate-400 pr-6">Action
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100/60 bg-white/30 {{ $compact }}">
                            @forelse ($dataheader as $fot)
                                @php
                                    $smart = OvertimePresenter::smartState($fot);
                                    $steps = $fot->approvalRequest?->steps ?? collect();
                                @endphp
                                <tr wire:key="row-{{ $fot->id }}"
                                    class="group hover:bg-indigo-50/20 transition-all duration-200"
                                    :class="selectedIds.includes('{{ $fot->id }}') ? 'bg-indigo-50/40' : ''">
                                    {{-- Selection --}}
                                    @if ($canApprove)
                                        <td class="px-6 py-4">
                                            <input type="checkbox" x-model="selectedIds" value="{{ $fot->id }}"
                                                class="row-checkbox rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        </td>
                                    @endif

                                    {{-- Reference & Type --}}
                                    <td class="{{ $rowPadding }} whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span
                                                class="font-black text-slate-800 tabular-nums">#{{ $fot->id }}</span>
                                            <span
                                                class="mt-1 inline-flex items-center gap-0.5 rounded px-1.5 py-0.5 text-[9px] font-black uppercase w-fit
                                            {{ $fot->is_planned ? 'bg-indigo-50 text-indigo-600' : 'bg-rose-50 text-rose-600 border border-rose-100/50' }}">
                                                {{ $fot->is_planned ? 'Planned' : 'Urgent' }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Creator --}}
                                    <td class="{{ $rowPadding }} whitespace-nowrap">
                                        <div class="font-bold text-slate-800">{{ $fot->user?->name ?? 'Unknown' }}</div>
                                    </td>

                                    {{-- Dept/Branch --}}
                                    <td class="{{ $rowPadding }} whitespace-nowrap">
                                        <span
                                            class="inline-flex rounded border border-slate-200 bg-slate-50 px-1.5 py-0.5 text-[10px] font-black text-slate-600">
                                            {{ $fot->department?->name ?? '-' }}
                                        </span>
                                        <div class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-0.5">
                                            {{ $fot->branch }}</div>
                                    </td>

                                    {{-- Date --}}
                                    <td class="{{ $rowPadding }} whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <div class="font-bold text-slate-700 tabular-nums">
                                                {{ $fot->first_overtime_date ? date('D, d M Y', strtotime($fot->first_overtime_date)) : '—' }}
                                            </div>
                                            <div class="text-[9px] text-slate-400 mt-0.5">Submitted
                                                {{ $fot->created_at?->diffForHumans() }}</div>
                                        </div>
                                    </td>

                                    {{-- Consolidated Status --}}
                                    <td class="{{ $rowPadding }} whitespace-nowrap border-r border-slate-50">
                                        <div class="flex flex-col gap-1 min-w-[120px]">

                                            @if ($smart['stage'] === 'signing')
                                                {{-- SIGNING PHASE --}}
                                                <span
                                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wide w-fit {{ $smart['classes'] }}">
                                                    <i class="bx {{ $smart['icon'] }} text-xs"></i>
                                                    {{ $smart['label'] }} <span
                                                        class="opacity-50 ml-1">{{ $smart['signed_steps'] }}/{{ $smart['total_steps'] }}</span>
                                                </span>
                                                <div class="flex items-center gap-1 text-[9px] font-bold text-slate-400 truncate max-w-[120px] px-1"
                                                    title="Next: {{ $smart['current_actor'] }}">
                                                    <i class='bx bx-right-arrow-alt'></i>
                                                    {{ $smart['current_actor'] ?? 'Awaiting' }}
                                                </div>
                                            @elseif($smart['stage'] === 'audit')
                                                {{-- AUDIT PHASE --}}
                                                <span
                                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wide w-fit {{ $smart['classes'] }}">
                                                    <i class="bx {{ $smart['icon'] }} text-xs"></i>
                                                    {{ $smart['label'] ?? 'Review' }} <span
                                                        class="opacity-50 ml-1">{{ $fot->approved_count + $fot->rejected_count }}/{{ $fot->details_count }}</span>
                                                </span>
                                                <div
                                                    class="text-[9px] font-black text-slate-300 uppercase tracking-tighter px-1">
                                                    Awaiting Detail Review</div>
                                            @elseif($smart['stage'] === 'sync' || $smart['stage'] === 'rejected')
                                                {{-- RESULT PHASE --}}
                                                <span
                                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wide w-fit {{ $smart['classes'] }}"
                                                    @if (isset($smart['reason'])) title="{{ $smart['reason'] }}" @endif>
                                                    <i class="bx {{ $smart['icon'] }} text-xs"></i>
                                                    {{ $smart['label'] }}
                                                </span>

                                                {{-- Success Fraction (x/y approach) --}}
                                                <div class="flex items-center text-[10px] font-black tabular-nums mt-0.5 px-1"
                                                    title="{{ $fot->approved_count }} Approved, {{ $fot->rejected_count }} Rejected out of {{ $fot->details_count }}">
                                                    <span class="text-emerald-500">{{ $fot->approved_count }}</span>
                                                    <span class="text-slate-300 mx-0.5">/</span>
                                                    <span class="text-slate-400">{{ $fot->details_count }}</span>
                                                </div>

                                                @if (isset($smart['reason']))
                                                    <div class="text-[9px] text-rose-500 font-bold max-w-[140px] truncate px-1 mt-0.5"
                                                        title="{{ $smart['reason'] }}">
                                                        {{ $smart['reason'] }}
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Simple Actions --}}
                                    <td class="{{ $rowPadding }} whitespace-nowrap text-right">
                                        <div
                                            class="flex items-center justify-end gap-1 opacity-40 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('overtime.detail', $fot->id) }}"
                                                class="inline-flex h-9 px-3 items-center justify-center gap-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-black text-slate-600 hover:bg-slate-800 hover:text-white hover:border-slate-800 transition-all shadow-sm">
                                                Manage <i class='bx bx-right-arrow-alt text-lg'></i>
                                            </a>
                                            @can('delete', $fot)
                                                <button wire:click="$dispatch('confirm-delete', { id: {{ $fot->id }} })"
                                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-transparent text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-all">
                                                    <i class='bx bx-trash'></i>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canApprove ? 7 : 6 }}" class="px-6 py-16 text-center">
                                        <div
                                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-50 text-slate-300 border border-slate-100 mb-4">
                                            <i class='bx bx-filter-alt text-3xl'></i>
                                        </div>
                                        <h3 class="text-sm font-black text-slate-700">No results match your filters</h3>
                                        <p class="text-xs text-slate-400 mt-1">Try adjusting your date range or removing
                                            some filters.</p>
                                        <button wire:click="resetFilters"
                                            class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-slate-100 px-4 py-2 text-xs font-black text-slate-600 hover:bg-slate-200 transition-all">
                                            <i class='bx bx-reset'></i> Clear All Filters
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Skeleton Loader --}}
                    @php $cols = $canApprove ? 7 : 6; @endphp
                    <table class="min-w-full" wire:loading wire:key="tbl-skeleton"
                        wire:target="resetFilters,setRange,sortBy,perPage,search,dept,startDate,endDate,infoStatus,clearFilter,gotoPage,nextPage,previousPage">
                        <tbody class="divide-y divide-slate-100/60">
                            @for ($i = 0; $i < min(8, $perPage); $i++)
                                <tr>
                                    @for ($j = 0; $j < $cols; $j++)
                                        <td class="{{ $rowPadding }} animate-pulse">
                                            <div class="h-4 rounded bg-slate-100"></div>
                                        </td>
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
         SECTION E: BULK ACTION BAR & SNAPSHOT DRAWER
    ================================================================ --}}

        {{-- Floating Action Bar --}}
        @if ($canApprove)
            <template x-teleport="body">
                <div x-cloak x-show="selectedIds.length > 0" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-10" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-10"
                    class="fixed bottom-10 left-1/2 -translate-x-1/2 z-[50]">
                    <div
                        class="bg-slate-900 border border-slate-700/50 rounded-2xl px-6 py-4 shadow-2xl flex items-center gap-6 min-w-[400px]">
                        <div class="flex items-center gap-3">
                            <div
                                class="h-10 w-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black">
                                <span x-text="selectedIds.length"></span>
                            </div>
                            <div>
                                <h4 class="text-xs font-black text-white uppercase tracking-tight">Entries Selected</h4>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">Ready for
                                    batch action</p>
                            </div>
                        </div>

                        <div class="h-8 w-px bg-slate-700 mx-2"></div>

                        <div class="flex items-center gap-3">
                            <button type="button" wire:click="loadSnapshot" wire:loading.attr="disabled"
                                class="h-10 px-6 rounded-xl bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 hover:-translate-y-0.5 transition-all shadow-lg shadow-indigo-500/20">
                                <span wire:loading.remove wire:target="loadSnapshot">Review & Sign Selected</span>
                                <span wire:loading wire:target="loadSnapshot" class="flex items-center gap-2">
                                    <i class='bx bx-loader-alt animate-spin'></i> Analyzing...
                                </span>
                            </button>
                            <button type="button" @click="selectedIds = []"
                                class="text-[10px] font-black text-slate-400 hover:text-white uppercase tracking-widest transition-colors">Dismiss</button>
                        </div>
                    </div>
                </div>
            </template>
        @endif

        {{-- Decision Intelligence Drawer --}}
        <template x-teleport="body">
            <div x-cloak x-show="$wire.showSnapshot" class="relative z-[150]">
                {{-- Backdrop --}}
                <div x-show="$wire.showSnapshot" x-transition.opacity
                    class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm" @click="$wire.showSnapshot = false"></div>

                <div class="fixed inset-y-0 right-0 z-[160] w-full max-w-lg md:max-w-xl flex">
                    <div x-show="$wire.showSnapshot"
                        x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
                        x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                        x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
                        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                        class="w-full bg-white shadow-2xl flex flex-col pointer-events-auto">

                        {{-- Drawer Header --}}
                        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                            <div class="flex items-center gap-4">
                                <div
                                    class="h-12 w-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-lg">
                                    <i class='bx bx-file-find text-2xl'></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight">Batch Approval
                                        Snapshot</h3>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                                        Decision Intelligence Summary</p>
                                </div>
                            </div>
                            <button @click="$wire.showSnapshot = false"
                                class="h-10 w-10 rounded-xl hover:bg-slate-100 transition-all flex items-center justify-center text-slate-400">
                                <i class='bx bx-x text-2xl'></i>
                            </button>
                        </div>

                        {{-- Drawer Body --}}
                        <div class="flex-1 overflow-y-auto px-8 py-8 custom-scrollbar space-y-8">

                            {{-- Aggregates --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="p-5 rounded-2xl bg-indigo-50 border border-indigo-100">
                                    <span
                                        class="text-[9px] font-black text-indigo-400 uppercase tracking-widest block mb-2">Total
                                        Volume</span>
                                    <div class="flex items-end gap-2 text-indigo-900">
                                        <span
                                            class="text-4xl font-black leading-none">{{ $snapshot['total_hours'] ?? 0 }}</span>
                                        <span class="text-xs font-black uppercase mb-1">Total Hours</span>
                                    </div>
                                </div>
                                <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-100">
                                    <span
                                        class="text-[9px] font-black text-emerald-400 uppercase tracking-widest block mb-2">Unique
                                        Entities</span>
                                    <div class="flex items-end gap-2 text-emerald-900">
                                        <span
                                            class="text-4xl font-black leading-none">{{ $snapshot['total_employees'] ?? 0 }}</span>
                                        <span class="text-xs font-black uppercase mb-1">Employees</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Date & Dept Context --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-3">
                                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Temporal
                                        Span</h4>
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 p-3 rounded-xl bg-slate-50 border border-slate-100">
                                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                                Start</p>
                                            <p class="text-xs font-black text-slate-700">
                                                {{ $snapshot['date_range']['start'] ?? '-' }}</p>
                                        </div>
                                        <i class='bx bx-right-arrow-alt text-slate-300'></i>
                                        <div class="flex-1 p-3 rounded-xl bg-slate-50 border border-slate-100">
                                            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1">
                                                End</p>
                                            <p class="text-xs font-black text-slate-700">
                                                {{ $snapshot['date_range']['end'] ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Departments
                                    </h4>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($snapshot['departments'] ?? [] as $name => $count)
                                            <span
                                                class="px-2 py-1 rounded-lg bg-slate-100 border border-slate-200 text-[10px] font-bold text-slate-600">
                                                {{ $name }} <span
                                                    class="ml-1 opacity-50">x{{ $count }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            {{-- Heuristic Warnings --}}
                            @if (!empty($warnings))
                                <div class="p-6 rounded-3xl bg-rose-50 border border-rose-100 space-y-4">
                                    <div class="flex items-center gap-3 text-rose-600">
                                        <i class='bx bx-error-alt text-2xl'></i>
                                        <h4 class="text-sm font-black uppercase tracking-tight">Risk Anomalies Detected
                                        </h4>
                                    </div>
                                    <div class="space-y-3">
                                        @if (isset($warnings['overlaps']))
                                            <div class="space-y-1">
                                                <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest">
                                                    Session Conflicts</p>
                                                @foreach ($warnings['overlaps'] as $overlap)
                                                    <p class="text-xs font-medium text-rose-700 leading-relaxed">•
                                                        {{ $overlap }}</p>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if (isset($warnings['intensity']))
                                            <div class="space-y-1">
                                                <p class="text-[10px] font-black text-rose-500 uppercase tracking-widest">
                                                    Workload Intensity</p>
                                                <p class="text-xs font-medium text-rose-700">•
                                                    {{ $warnings['intensity'] }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="p-8 rounded-3xl bg-emerald-50 border border-emerald-100 text-center">
                                    <div
                                        class="h-12 w-12 rounded-full bg-emerald-500 text-white flex items-center justify-center mx-auto mb-4 shadow-lg shadow-emerald-100">
                                        <i class='bx bx-check text-2xl'></i>
                                    </div>
                                    <h4 class="text-sm font-black text-emerald-900 uppercase">Heuristics Clear</h4>
                                    <p class="text-xs text-emerald-600 mt-1">No session overlaps or threshold violations
                                        detected in this batch.</p>
                                </div>
                            @endif

                            <div class="h-px bg-slate-100"></div>
                            <p class="text-[10px] font-medium text-slate-400 text-center leading-relaxed italic">
                                By proceeding, your digital signature will be applied to all <span
                                    x-text="selectedIds.length"></span> selected forms. Ensure you have reviewed the
                                aggregate data above correctly.
                            </p>
                        </div>

                        {{-- Drawer Footer --}}
                        <div class="p-8 border-t border-slate-100 flex items-center gap-3 bg-slate-50/30">
                            <button type="button" @click="$wire.showSnapshot = false"
                                class="flex-1 h-12 rounded-2xl border border-slate-200 bg-white text-[11px] font-black text-slate-600 uppercase tracking-widest hover:bg-slate-50 transition-all">
                                Back to Index
                            </button>
                            <button type="button" wire:click="bulkApprove" wire:loading.attr="disabled"
                                class="flex-[1.5] h-12 rounded-2xl bg-emerald-600 text-[11px] font-black text-white uppercase tracking-widest shadow-xl shadow-emerald-100 hover:bg-emerald-700 hover:-translate-y-0.5 transition-all">
                                <span wire:loading.remove wire:target="bulkApprove">Confirm Batch Approval</span>
                                <span wire:loading wire:target="bulkApprove"><i class='bx bx-loader-alt animate-spin'></i>
                                    Signing...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="h-20"></div> {{-- Spacer for floating bar --}}
        <template x-teleport="body">
            <div x-cloak x-show="deleteOpen" class="relative z-[60]">
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" x-show="deleteOpen" x-transition.opacity
                    @click="deleteOpen = false"></div>
                <div class="fixed inset-0 z-[70] flex items-center justify-center p-4" x-show="deleteOpen" x-transition
                    role="dialog" aria-modal="true">
                    <div class="w-full max-w-sm rounded-3xl bg-white/95 backdrop-blur-2xl shadow-2xl border border-white/80 p-6 text-center relative overflow-hidden"
                        @click.stop>
                        <div
                            class="absolute -top-10 -right-10 h-28 w-28 rounded-full bg-rose-50 blur-2xl pointer-events-none">
                        </div>
                        <div
                            class="absolute -bottom-10 -left-10 h-28 w-28 rounded-full bg-rose-50 blur-2xl pointer-events-none">
                        </div>
                        <div class="relative z-10">
                            <div
                                class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100 text-rose-600">
                                <i class='bx bx-trash text-3xl'></i>
                            </div>
                            <h3 class="text-base font-black text-slate-800">Delete OT-{{ $pendingDeleteId }}?</h3>
                            <p class="mt-2 text-xs text-slate-500 leading-relaxed mb-5">
                                This will permanently remove all detail rows and approval data for this form. This action
                                cannot be undone.
                            </p>
                            <div class="flex gap-2">
                                <button @click="deleteOpen = false"
                                    class="flex-1 rounded-xl border border-slate-200 bg-white py-2.5 text-xs font-black text-slate-600 hover:bg-slate-50 transition-all">Cancel</button>
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
