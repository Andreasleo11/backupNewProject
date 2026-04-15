<div>
    @section('page-title', 'Department Expenses')
    @section('page-subtitle', 'Financial summary and drill-down analysis drawn from PR and Budget operations')

    @php
        $monthLabel = \Illuminate\Support\Carbon::parse(($month ?? now()->format('Y-m')) . '-01')->isoFormat(
            'MMMM YYYY',
        );
        $grandTotal = $this->totals()->sum('total_expense');
        $deptSelected = $deptId ? optional($this->totals()->firstWhere('dept_id', $deptId))->dept_name : null;
    @endphp

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-8">

        {{-- Header Section (Premium Admin Dashboard Style) --}}
        <div class="relative z-50 rounded-3xl bg-slate-900 shadow-2xl">
            {{-- Background glow with overflow hidden to prevent spilling --}}
            <div class="absolute inset-0 rounded-3xl overflow-hidden pointer-events-none">
                <div class="absolute right-0 top-0 -mr-16 -mt-16 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -ml-16 -mb-16 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl">
                </div>
            </div>

            <div class="relative px-8 py-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                {{-- Left: title --}}
                <div class="flex items-center gap-6">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-emerald-500 text-white shadow-xl shadow-indigo-500/20 shrink-0">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight text-white sm:text-4xl">
                            Department Expenses
                        </h1>
                        <p class="mt-2 max-w-2xl text-[15px] font-medium text-slate-400">
                            Monthly aggregated expenses unified from Purchase Requests & Budget summaries.
                        </p>
                    </div>
                </div>

                {{-- Right: filters (Native selects with high z-index and sleek dark UI to match) --}}
                <div
                    class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 w-full lg:w-auto relative z-50">
                    <div class="relative w-full sm:w-48 z-20">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="bi bi-calendar4-week text-slate-400"></i>
                        </div>
                        <select
                            class="block w-full rounded-xl border-slate-700 bg-slate-800/80 py-2.5 pl-9 pr-8 text-sm font-semibold text-white shadow-lg focus:border-indigo-500 focus:ring-indigo-500 outline-none appearance-none transition-colors"
                            wire:model.live="month"
                            style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394a3b8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.75rem top 50%; background-size: 0.65rem auto;">
                            <option>
                                @foreach ($this->monthOptions() as $opt)
                            <option value="{{ $opt['value'] }}" class="bg-slate-800">{{ $opt['label'] }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="relative w-full sm:w-56 z-10">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <i class="bi bi-person-badge text-slate-400"></i>
                        </div>
                        <select
                            class="block w-full rounded-xl border-slate-700 bg-slate-800/80 py-2.5 pl-9 pr-8 text-sm font-semibold text-white shadow-lg focus:border-indigo-500 focus:ring-indigo-500 outline-none appearance-none transition-colors"
                            wire:model.live="prSigner"
                            style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394a3b8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.75rem top 50%; background-size: 0.65rem auto;">
                            <option value="" class="bg-slate-800 text-slate-300">All Approvers</option>
                            @foreach ($this->prSigners() as $signer)
                                <option value="{{ $signer }}" class="bg-slate-800">{{ $signer }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Cards row (Matches Access Overview Stats Grid) --}}
        <div class="relative z-0 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Period KPI --}}
            <div
                class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                <div
                    class="absolute right-0 top-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-blue-50 transition-all group-hover:scale-150">
                </div>
                <div class="relative">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-none">
                        Selected Period</h3>
                    <p class="mt-2 text-2xl font-black text-slate-900 leading-none truncate"
                        title="{{ $monthLabel }}">{{ $monthLabel }}</p>
                </div>
            </div>

            {{-- Total Expense KPI --}}
            <div
                class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                <div
                    class="absolute right-0 top-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-emerald-50 transition-all group-hover:scale-150">
                </div>
                <div class="relative">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-none">
                        Cumulative Total</h3>
                    <p class="mt-2 text-2xl font-black text-slate-900 leading-none truncate"
                        title="Rp {{ number_format($grandTotal, 0, ',', '.') }}">
                        Rp {{ number_format($grandTotal, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- Departments KPI --}}
            <div
                class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                <div
                    class="absolute right-0 top-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-indigo-50 transition-all group-hover:scale-150">
                </div>
                <div class="relative">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-none">
                        Departments Count</h3>
                    <p class="mt-2 text-2xl font-black text-slate-900 leading-none truncate">
                        {{ $this->totals()->count() }} active</p>
                </div>
            </div>

            {{-- Selected Status --}}
            <div
                class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all hover:shadow-xl hover:-translate-y-1">
                <div
                    class="absolute right-0 top-0 -mr-4 -mt-4 h-24 w-24 rounded-full bg-amber-50 transition-all group-hover:scale-150">
                </div>
                <div class="relative">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $deptSelected || $prSigner ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-400' }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                    </div>
                    <h3 class="mt-4 text-[11px] font-bold text-slate-400 uppercase tracking-widest leading-none">Data
                        Filter Context</h3>
                    @if ($deptSelected)
                        <p class="mt-2 text-2xl font-black text-slate-900 leading-none truncate"
                            title="{{ $deptSelected }}">{{ $deptSelected }}</p>
                    @elseif ($prSigner)
                        <p class="mt-2 text-2xl font-black text-slate-900 leading-none truncate"
                            title="{{ $prSigner }}">{{ $prSigner }}</p>
                    @else
                        <p class="mt-2 text-2xl font-black text-slate-900 leading-none truncate">System-wide</p>
                    @endif
                </div>
            </div>
        </div>

        <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm" wire:key="dept-expenses-chart"
            x-data="departmentChart()" x-init="init({
                labels: @js($this->totals()->pluck('dept_name')->values()),
                data: @js($this->totals()->pluck('total_expense')->map(fn($v) => (float) $v)->values()),
                deptIds: @js($this->totals()->pluck('dept_id')->map(fn($v) => (int) $v)->values())
            })">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Expenses Breakdown</h2>
                    <p class="text-sm text-slate-500 mt-1">Aggregate comparison of
                        {{ \Illuminate\Support\Carbon::parse($month . '-01')->isoFormat('MMMM YYYY') }}.</p>
                </div>
                <div class="p-2 rounded-xl bg-slate-50 text-slate-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>

            <div class="relative h-[380px]">
                <div wire:ignore class="h-full w-full">
                    <canvas x-ref="canvas" role="img" aria-label="Department expenses bar chart"></canvas>
                </div>

                {{-- Skeleton overlay loading --}}
                <div class="pointer-events-none absolute inset-0 bg-white/60 backdrop-blur-sm flex items-center justify-center transition-opacity duration-300 opacity-0"
                    wire:loading.class="opacity-100" wire:target="month,prSigner,showDetail,clearDetail">
                    <div class="flex items-end gap-2 h-32 opacity-50">
                        <div class="w-8 bg-indigo-200 animate-pulse rounded-t-sm h-12"></div>
                        <div class="w-8 bg-indigo-200 animate-pulse rounded-t-sm h-24" style="animation-delay: 150ms">
                        </div>
                        <div class="w-8 bg-indigo-200 animate-pulse rounded-t-sm h-32" style="animation-delay: 300ms">
                        </div>
                        <div class="w-8 bg-indigo-200 animate-pulse rounded-t-sm h-16" style="animation-delay: 450ms">
                        </div>
                        <div class="w-8 bg-indigo-200 animate-pulse rounded-t-sm h-20" style="animation-delay: 600ms">
                        </div>
                    </div>
                </div>
            </div>

            @if ($deptId)
                <div class="mt-2 flex justify-end">
                    <button type="button"
                        class="inline-flex items-center text-xs font-medium text-slate-500 hover:text-slate-700"
                        wire:click="clearDetail">
                        <i class="bi bi-x-circle mr-1"></i>
                        Clear highlight
                    </button>
                </div>
            @endif
    </div>
    </section>

    {{-- Content area: Segmented Control Tabs --}}
    <section class="rounded-3xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        {{-- Custom pill-shaped tabs --}}
        <div class="border-b border-slate-100 bg-slate-50/50 p-6">
            <div
                class="inline-flex items-center space-x-2 p-1 bg-slate-200/50 backdrop-blur-sm rounded-xl border border-slate-200/50">
                <button type="button" wire:click="$set('activeTab','overview')" @class([
                    'relative flex items-center justify-center px-4 py-1.5 text-sm font-semibold transition-all duration-200 ease-out rounded-md outline-none focus:ring-2 focus:ring-indigo-500',
                    $activeTab === 'overview'
                        ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50'
                        : 'text-slate-500 hover:text-slate-800 hover:bg-slate-200/50',
                ])>
                    <i class="bi bi-table mr-2 opacity-75"></i>
                    Overview
                </button>

                <button type="button" wire:click="$set('activeTab','detail')" @class([
                    'relative flex items-center justify-center px-4 py-1.5 text-sm font-semibold transition-all duration-200 ease-out rounded-md outline-none focus:ring-2 focus:ring-indigo-500',
                    $activeTab === 'detail'
                        ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50'
                        : 'text-slate-500 hover:text-slate-800 hover:bg-slate-200/50',
                ])>
                    <i class="bi bi-list-ul mr-2 opacity-75"></i>
                    <span class="flex items-center gap-2">
                        Details
                        @if ($activeTab === 'detail' && $deptSelected)
                            <span
                                class="hidden sm:inline-flex bg-indigo-100 text-indigo-800 text-[10px] uppercase font-bold px-1.5 py-0.5 rounded-sm">{{ $deptSelected }}</span>
                        @endif
                    </span>
                </button>

                <button type="button" wire:click="$set('activeTab','compare')" @class([
                    'relative flex items-center justify-center px-4 py-1.5 text-sm font-semibold transition-all duration-200 ease-out rounded-md outline-none focus:ring-2 focus:ring-indigo-500',
                    $activeTab === 'compare'
                        ? 'bg-white text-indigo-700 shadow-sm ring-1 ring-slate-200/50'
                        : 'text-slate-500 hover:text-slate-800 hover:bg-slate-200/50',
                ])>
                    <i class="bi bi-arrow-left-right mr-2 opacity-75"></i>
                    Compare
                </button>
            </div>
        </div>

        {{-- Tab panes --}}
        <div class="p-6 relative z-20 min-h-[400px]">
            {{-- Loading spinner overlay --}}
            <div wire:loading.delay wire:target="activeTab"
                class="absolute inset-0 z-10 flex items-center justify-center bg-white/60 backdrop-blur-sm rounded-b-2xl">
                <i class="bi bi-arrow-repeat animate-spin text-3xl text-indigo-500"></i>
            </div>

            {{-- OVERVIEW --}}
            @if ($activeTab === 'overview')
                <div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead>
                                <tr
                                    class="border-b border-slate-200 text-xs font-semibold uppercase tracking-wider text-slate-500 bg-slate-50/50">
                                    <th class="px-4 py-3">Department</th>
                                    <th class="px-4 py-3">Dept No</th>
                                    <th class="px-4 py-3 text-right">Total Expense</th>
                                    <th class="px-4 py-3 text-right"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($this->totals as $r)
                                    <tr class="hover:bg-slate-50/70 transition-colors duration-150">
                                        <td class="px-3 py-2 font-medium text-slate-900">
                                            {{ $r->dept_name }}
                                        </td>
                                        <td class="px-3 py-2 text-slate-500">
                                            {{ $r->dept_no }}
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold">
                                            Rp {{ number_format($r->total_expense, 0, ',', '.') }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button type="button"
                                                class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:border-indigo-300 hover:bg-slate-50 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
                                                wire:click="showDetail({{ (int) $r->dept_id }})">
                                                View lines
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-3 py-6 text-center text-slate-400 text-sm">
                                            <i class="bi bi-inbox mr-1"></i>
                                            No data for {{ $monthLabel }}.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                            @if ($this->totals->count())
                                <tfoot>
                                    <tr class="border-t border-slate-200 bg-slate-50/80 text-sm">
                                        <th colspan="2" class="px-3 py-2 text-right font-medium text-slate-700">
                                            Grand Total
                                        </th>
                                        <th class="px-3 py-2 text-right font-semibold text-slate-900">
                                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                                        </th>
                                        <th class="px-3 py-2"></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            @endif

            {{-- DETAIL --}}
            @if ($activeTab === 'detail')
                <div>
                    @if (!$deptId)
                        <div class="rounded-lg border border-sky-100 bg-sky-50 px-3 py-2 text-xs text-sky-700">
                            <i class="bi bi-info-circle mr-1"></i>
                            Click a bar in the chart or the “View lines” button in the table to see details.
                        </div>
                    @else
                        <livewire:reports.department-expense-detail-table :dept-id="$deptId" :month="$month"
                            :dept-name="$deptSelected ?? $deptId" :month-label="$monthLabel" :pr-signer="$prSigner" :key="'detail-' . $deptId . '-' . $month . '-' . ($prSigner ?? 'all')" />
                    @endif
                </div>
            @endif

            {{-- COMPARE --}}
            @if ($activeTab === 'compare')
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-6 mb-8"
                        x-data="compareChart()" x-init="mount()">
                        <div>
                            <h3 class="text-xl font-bold tracking-tight text-slate-900">Compare Analytics</h3>
                            <p class="text-sm font-medium text-slate-500 mt-1">Cross-reference spending across
                                timeframes.</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-4">
                            {{-- Mode toggle --}}
                            <div
                                class="inline-flex items-center space-x-1 p-1 bg-slate-100 rounded-xl border border-slate-200">
                                <label class="cursor-pointer">
                                    <input type="radio" class="sr-only" value="range"
                                        wire:model.live="compareMode">
                                    <div
                                        class="px-3 py-1.5 rounded-lg text-sm font-bold transition-all {{ $compareMode === 'range' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                                        <i class="bi bi-calendar-range mr-1"></i> Range
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" class="sr-only" value="rolling"
                                        wire:model.live="compareMode">
                                    <div
                                        class="px-3 py-1.5 rounded-lg text-sm font-bold transition-all {{ $compareMode === 'rolling' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                                        <i class="bi bi-arrow-counterclockwise mr-1"></i> Rolling
                                    </div>
                                </label>
                            </div>

                            <div class="hidden xl:block h-8 w-px bg-slate-200"></div>

                            {{-- Range controls --}}
                            @if ($compareMode === 'range')
                                <div
                                    class="flex flex-wrap items-center gap-3 bg-slate-50 p-1.5 rounded-xl border border-slate-200/60 relative z-50">
                                    <div class="relative w-40 z-20">
                                        <div
                                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <i class="bi bi-calendar-event text-slate-400"></i>
                                        </div>
                                        <select
                                            class="block w-full rounded-lg border-0 bg-white py-2 pl-9 pr-8 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-indigo-600 appearance-none transition-colors"
                                            wire:model.live="startMonth"
                                            style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394a3b8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.5rem top 50%; background-size: 0.5rem auto;">
                                            @foreach ($this->monthOptions() as $opt)
                                                <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <span class="text-[10px] font-black uppercase text-slate-400 z-0">to</span>
                                    <div class="relative w-40 z-10">
                                        <div
                                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <i class="bi bi-calendar-event text-slate-400"></i>
                                        </div>
                                        <select
                                            class="block w-full rounded-lg border-0 bg-white py-2 pl-9 pr-8 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-indigo-600 appearance-none transition-colors"
                                            wire:model.live="endMonth"
                                            style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394a3b8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.5rem top 50%; background-size: 0.5rem auto;">
                                            @foreach ($this->monthOptions() as $opt)
                                                <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @else
                                {{-- Rolling controls --}}
                                <div
                                    class="flex flex-wrap items-center gap-3 bg-slate-50 p-1.5 rounded-xl border border-slate-200/60 relative z-50">
                                    <div class="relative w-40 z-20">
                                        <div
                                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <i class="bi bi-calendar-event text-slate-400"></i>
                                        </div>
                                        <select
                                            class="block w-full rounded-lg border-0 bg-white py-2 pl-9 pr-8 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-indigo-600 appearance-none transition-colors"
                                            wire:model.live="endMonth"
                                            style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394a3b8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.5rem top 50%; background-size: 0.5rem auto;">
                                            @foreach ($this->monthOptions() as $opt)
                                                <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="relative w-32 z-10">
                                        <select
                                            class="block w-full rounded-lg border-0 bg-white py-2 pl-3 pr-8 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-indigo-600 appearance-none transition-colors"
                                            wire:model.live="rollingN"
                                            style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%2394a3b8%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.5rem top 50%; background-size: 0.5rem auto;">
                                            <option value="3">Last 3 Months</option>
                                            <option value="4">Last 4 Months</option>
                                            <option value="6">Last 6 Months</option>
                                            <option value="12">Last 12 Months</option>
                                        </select>
                                    </div>
                                </div>
                            @endif

                            <div
                                class="hidden sm:flex items-center gap-4 bg-slate-50 p-2 rounded-xl border border-slate-200/60 ml-auto">
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox"
                                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600 transition-shadow"
                                        x-model="stacked" @change="update()">
                                    <span
                                        class="text-sm font-semibold text-slate-600 group-hover:text-slate-900 transition-colors">Stacked</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer group">
                                    <input type="checkbox"
                                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600 transition-shadow"
                                        x-model="normalized" @change="update()">
                                    <span
                                        class="text-sm font-semibold text-slate-600 group-hover:text-slate-900 transition-colors">Normalize
                                        (%)</span>
                                </label>
                            </div>
                        </div>

                        {{-- Compare chart --}}
                        <div
                            class="relative mt-8 h-[450px] w-full rounded-2xl bg-slate-50 border border-slate-100 p-4">
                            <div wire:ignore class="h-full w-full">
                                <canvas x-ref="canvas"></canvas>
                            </div>
                            <div class="absolute inset-0 z-10 flex items-center justify-center rounded-2xl bg-white/60 backdrop-blur-sm transition-opacity duration-300"
                                wire:loading wire:target="compareMode,startMonth,endMonth,rollingN,prSigner">
                                <div
                                    class="flex items-center gap-3 rounded-full bg-white px-5 py-2.5 text-sm font-bold text-indigo-600 shadow-lg ring-1 ring-slate-100">
                                    <svg class="h-5 w-5 animate-spin text-indigo-600"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Regenerating Analytics
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Δ table (range & 2 months) --}}
                    @if (($compareMonths ?? null) && count($compareMonths) === 2 && ($compareDeltas ?? null))
                        @php
                            [$m0, $m1] = $compareMonths;
                            $ml0 = \Illuminate\Support\Carbon::parse($m0 . '-01')->isoFormat('MMM YYYY');
                            $ml1 = \Illuminate\Support\Carbon::parse($m1 . '-01')->isoFormat('MMM YYYY');
                        @endphp

                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full text-left text-xs sm:text-sm text-slate-700">
                                <thead>
                                    <tr class="border-b border-slate-200 text-[11px] uppercase text-slate-500">
                                        <th class="px-3 py-2 font-medium">Department</th>
                                        <th class="px-3 py-2 text-right font-medium">{{ $ml0 }}</th>
                                        <th class="px-3 py-2 text-right font-medium">{{ $ml1 }}</th>
                                        <th class="px-3 py-2 text-right font-medium">Δ</th>
                                        <th class="px-3 py-2 text-right font-medium">Δ%</th>
                                        <th class="px-3 py-2 text-right font-medium"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($compareDeltas as $row)
                                        @php
                                            $pos = $row->diff > 0;
                                            $zero = $row->diff == 0;
                                            $badgeClasses = $zero
                                                ? 'bg-slate-100 text-slate-700'
                                                : ($pos
                                                    ? 'bg-emerald-50 text-emerald-700'
                                                    : 'bg-rose-50 text-rose-700');
                                        @endphp
                                        <tr class="hover:bg-indigo-50/40">
                                            <td class="px-3 py-2">{{ $row->dept_name }}</td>
                                            <td class="px-3 py-2 text-right">
                                                Rp {{ number_format($row->a, 0, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                Rp {{ number_format($row->b, 0, ',', '.') }}
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                <span
                                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeClasses }}">
                                                    {{ $pos ? '+' : '' }}Rp
                                                    {{ number_format($row->diff, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                @if ($row->pct === null)
                                                    <span class="text-slate-400">—</span>
                                                @else
                                                    <span
                                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeClasses }}">
                                                        {{ ($row->pct > 0 ? '+' : '') . number_format($row->pct, 1, ',', '.') }}%
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                <button type="button"
                                                    class="inline-flex items-center rounded-md border border-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700 hover:border-indigo-300 hover:text-indigo-600"
                                                    wire:click="showDetail({{ $row->dept_id }})">
                                                    View lines
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>
</div>

</div>

{{-- Chart.js + Alpine controllers --}}
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        function departmentChart() {
            return {
                chart: null,
                labels: [],
                data: [],
                deptIds: [],
                selectedDeptId: null,

                init(initial = {}) {
                    this.setData(initial);
                    this.mountChart();

                    // Livewire -> refresh dataset when month changes
                    window.addEventListener('chart:render', (e) => {
                        const payload = e.detail?.data ?? {};
                        requestAnimationFrame(() => {
                            this.setData(payload); // reset selection on new month
                            this.updateChart();
                        });
                    });

                    if (!this._boundClear) {
                        this._boundClear = true;
                        window.addEventListener('chart:clearSelection', () => this.clearSelection(), {
                            passive: true
                        });
                    }

                    if (!this._boundHighlight) {
                        this._boundHighlight = true;
                        window.addEventListener('chart:highlightDept', (e) => {
                            this.selectedDeptId = Number(e.detail?.deptId ?? null);
                            this.updateChart();
                        }, {
                            passive: true
                        });
                    }
                },

                clearSelection() {
                    this.selectedDeptId = null;
                    this.updateChart();
                },

                setData(payload = {}) {
                    this.labels = Array.isArray(payload.labels) ? payload.labels : [];
                    this.data = Array.isArray(payload.data) ? payload.data.map(Number) : [];
                    this.deptIds = Array.isArray(payload.deptIds) ? payload.deptIds.map(Number) : [];
                    this.selectedDeptId = null;
                },

                // Premium design colors for main bar chart
                makeColors(ctx) {
                    if (!ctx) return {
                        bg: [],
                        bd: []
                    };
                    const isSelected = (id) => this.selectedDeptId !== null && id === this.selectedDeptId;
                    const hasSelection = this.selectedDeptId !== null;

                    return {
                        backgroundColor: this.deptIds.map(id => {
                            // If a bar is selected, heavily dim the unselected bars
                            if (hasSelection && !isSelected(id)) {
                                return 'rgba(203, 213, 225, 0.3)'; // slate-300 very faint
                            }

                            // Creates a vertical gradient from indigo-500 to emerald-400
                            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                            gradient.addColorStop(0, 'rgba(99, 102, 241, 0.9)'); // indigo-500
                            gradient.addColorStop(1, 'rgba(52, 211, 153, 0.9)'); // emerald-400
                            return gradient;
                        }),
                        borderColor: this.deptIds.map(id => {
                            if (hasSelection && !isSelected(id)) return 'transparent';
                            return 'rgba(79, 70, 229, 0.1)'; // faint indigo border
                        }),
                    };
                },

                mountChart() {
                    const canvas = this.$refs.canvas;
                    if (!canvas) return;

                    const existing = Chart.getChart(canvas);
                    if (existing) existing.destroy();
                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }

                    const ctx = canvas.getContext('2d');
                    const fmt = new Intl.NumberFormat('id-ID');
                    const col = this.makeColors(ctx);

                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: this.labels,
                            datasets: [{
                                label: 'Total Expense (IDR)',
                                data: this.data,
                                borderWidth: 0,
                                borderRadius: 6,
                                borderSkipped: false,
                                backgroundColor: col.backgroundColor,
                                hoverBackgroundColor: 'rgba(79, 70, 229, 1)', // Solid indigo on hover
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                                    titleColor: '#fff',
                                    bodyColor: '#e2e8f0',
                                    padding: 12,
                                    cornerRadius: 8,
                                    displayColors: false,
                                    callbacks: {
                                        label: (c) => 'Rp ' + fmt.format(c.parsed.y)
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(226, 232, 240, 0.6)', // slate-200
                                        drawBorder: false,
                                    },
                                    border: {
                                        display: false
                                    },
                                    ticks: {
                                        color: '#64748b', // slate-500
                                        font: {
                                            size: 11,
                                            family: 'Inter, sans-serif'
                                        },
                                        callback: (v) => 'Rp ' + (v >= 1000000 ? (v / 1000000).toFixed(0) + 'M' :
                                            fmt.format(v))
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    border: {
                                        display: false
                                    },
                                    ticks: {
                                        color: '#475569', // slate-600
                                        font: {
                                            size: 11,
                                            font: 'Inter, sans-serif'
                                        },
                                        maxRotation: 45,
                                        minRotation: 0
                                    }
                                }
                            },
                            onClick: (evt) => {
                                const pts = this.chart.getElementsAtEventForMode(evt, 'nearest', {
                                    intersect: true
                                }, true);
                                if (!pts.length) return;
                                const idx = pts[0].index;
                                const deptId = this.deptIds[idx];

                                // 1) highlight instantly
                                this.selectedDeptId = deptId;
                                this.updateChart(); // recolor only, no data change

                                // 2) defer Livewire call (avoid re-entrancy)
                                const call = () => this.$wire.showDetail(deptId);
                                if ('requestIdleCallback' in window) requestIdleCallback(call);
                                else setTimeout(call, 0);
                            }
                        }
                    });
                },

                updateChart() {
                    const canvas = this.$refs.canvas;
                    if (!canvas) return;

                    const current = Chart.getChart(canvas);
                    if (!current || current !== this.chart) {
                        this.mountChart();
                        return;
                    }

                    current.data.labels = this.labels;
                    current.data.datasets[0].data = this.data;

                    const col = this.makeColors();
                    current.data.datasets[0].backgroundColor = col.backgroundColor;
                    current.data.datasets[0].borderColor = col.borderColor;

                    current.update('none');
                }
            }
        }
        // Alpine controller for the Compare chart
        function compareChart() {
            return {
                chart: null,
                raw: null, // { labels, deptIds, months, datasets:[{label, data:[]}, ...] }
                stacked: false,
                normalized: false,
                selDatasetIdx: null,
                selDeptIdx: null,

                mount() {
                    if (!this._bound) {
                        this._bound = true;
                        window.addEventListener('compare:render', (e) => {
                            this.raw = e.detail?.data || null;
                            this.buildOrUpdate();
                        }, {
                            passive: true
                        });
                    }
                },

                // Build a pleasant categorical palette for Compare Chart
                color(ctx, datasetIdx) {
                    const palettes = [
                        ['rgba(99, 102, 241, 0.85)', 'rgba(99, 102, 241, 1)'], // Indigo
                        ['rgba(52, 211, 153, 0.85)', 'rgba(52, 211, 153, 1)'], // Emerald
                        ['rgba(244, 63, 94, 0.85)', 'rgba(244, 63, 94, 1)'], // Rose
                        ['rgba(245, 158, 11, 0.85)', 'rgba(245, 158, 11, 1)'], // Amber
                        ['rgba(14, 165, 233, 0.85)', 'rgba(14, 165, 233, 1)'], // Sky
                        ['rgba(168, 85, 247, 0.85)', 'rgba(168, 85, 247, 1)'], // Purple
                    ];

                    // Loop gracefully if we exceed 6 months
                    const colorSet = palettes[datasetIdx % palettes.length];

                    if (!ctx) return {
                        bg: colorSet[0],
                        hoverBg: colorSet[1]
                    };

                    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
                    gradient.addColorStop(0, colorSet[1]);
                    gradient.addColorStop(1, colorSet[0]);

                    return {
                        bg: gradient,
                        hoverBg: colorSet[1],
                    };
                },

                // Compute normalized datasets (% of month total) if toggled
                normalizedDatasets() {
                    if (!this.raw) return [];
                    const base = this.raw.datasets || [];
                    const mCount = base.length;
                    if (mCount === 0) return [];

                    // totals per month (sum of all departments)
                    const totals = [];
                    for (let j = 0; j < mCount; j++) {
                        totals[j] = (base[j].data || []).reduce((a, b) => a + (Number(b) || 0), 0);
                    }

                    return base.map((ds, j) => ({
                        label: ds.label + ' (%)',
                        data: (ds.data || []).map(v => {
                            const val = Number(v) || 0;
                            const t = totals[j] || 1;
                            return (val / t) * 100;
                        }),
                    }));
                },

                buildOrUpdate() {
                    if (!this.raw) return;

                    const canvas = this.$refs.canvas;
                    if (!canvas) return;

                    // empty state
                    if (!this.raw || !Array.isArray(this.raw.datasets) || this.raw.datasets.length === 0) {
                        const ctx = canvas.getContext('2d');
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                        ctx.font = '12px system-ui';
                        ctx.fillStyle = '#6c757d';
                        ctx.fillText('No comparison data', 12, 24);
                        if (this.chart) {
                            this.chart.destroy();
                            this.chart = null;
                        }
                        return;
                    }

                    // destroy if exists
                    const existing = Chart.getChart(canvas);
                    if (existing) existing.destroy();
                    if (this.chart) {
                        this.chart.destroy();
                        this.chart = null;
                    }

                    const ctx = canvas.getContext('2d');
                    const fmt = new Intl.NumberFormat('id-ID');

                    const labels = this.raw.labels || [];
                    const deptIds = (this.raw.deptIds || []).map(Number);
                    const datasets = this.normalized ? this.normalizedDatasets() : (this.raw.datasets || []);

                    // decorate datasets with premium palette
                    const chartDs = datasets.map((ds, i) => {
                        const c = this.color(ctx, i);

                        // Dim other datasets if one specific bar is clicked
                        const bgColors = ds.data.map((_, barIdx) => {
                            if (this.selDatasetIdx !== null && this.selDeptIdx !== null) {
                                const isSelected = this.selDatasetIdx === i && this.selDeptIdx === barIdx;
                                return isSelected ? c.hoverBg : 'rgba(203, 213, 225, 0.3)'; // Dimmified
                            }
                            return c.bg;
                        });

                        return {
                            label: ds.label,
                            data: (ds.data || []).map(Number),
                            backgroundColor: bgColors,
                            hoverBackgroundColor: c.hoverBg,
                            borderRadius: 4,
                            borderSkipped: false,
                        };
                    });

                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: chartDs
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                mode: 'index',
                                intersect: false,
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        usePointStyle: true,
                                        boxWidth: 8,
                                        padding: 20,
                                        font: {
                                            family: 'Inter, sans-serif',
                                            size: 12,
                                            weight: '500'
                                        }
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                                    titleColor: '#fff',
                                    bodyColor: '#e2e8f0',
                                    padding: 12,
                                    cornerRadius: 8,
                                    itemSort: (a, b) => b.raw - a.raw, // Sort tooltip highest to lowest
                                    callbacks: {
                                        label: (ctx) => {
                                            const v = ctx.parsed.y;
                                            return this.normalized ?
                                                ` ${ctx.dataset.label}: ${v.toFixed(1)}%` :
                                                ` ${ctx.dataset.label}: Rp ${fmt.format(v)}`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    stacked: this.stacked,
                                    grid: {
                                        display: false
                                    },
                                    border: {
                                        display: false
                                    },
                                    ticks: {
                                        color: '#475569',
                                        font: {
                                            size: 11,
                                            family: 'Inter, sans-serif'
                                        }, // Changed 'font' to 'family'
                                        maxRotation: 45,
                                        minRotation: 0
                                    }
                                },
                                y: {
                                    stacked: this.stacked,
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(226, 232, 240, 0.6)',
                                        drawBorder: false,
                                    },
                                    border: {
                                        display: false
                                    },
                                    ticks: {
                                        color: '#64748b',
                                        font: {
                                            size: 11,
                                            family: 'Inter, sans-serif'
                                        },
                                        callback: (v) => this.normalized ? v + '%' : 'Rp ' + (v >= 1000000 ? (v /
                                            1000000).toFixed(0) + 'M' : fmt.format(v))
                                    }
                                }
                            },
                            onClick: (evt) => this.handleClick(evt, deptIds),
                        }
                    });
                },

                handleClick(evt, deptIds) {
                    if (!this.chart) return;
                    const pts = this.chart.getElementsAtEventForMode(evt, 'nearest', {
                        intersect: true
                    }, true);
                    if (!pts.length) return;

                    const {
                        datasetIndex: di,
                        index: bi
                    } = pts[0]; // which dataset (month) and which bar (dept)
                    this.selDatasetIdx = di;
                    this.selDeptIdx = bi;

                    // Re-build to apply selection colors
                    this.buildOrUpdate();

                    const deptId = deptIds[bi];
                    const ym = (this.raw.months || [])[di]; // e.g. '2025-03'
                    const canvas = this.$refs.canvas;
                    const host = canvas?.closest('[wire\\:id]');
                    if (!host) return;
                    const comp = Livewire.find(host.getAttribute('wire:id'));
                    if (!comp) return;

                    comp.set('skipChartClearOnce', true);
                    comp.set('month', ym);
                    comp.set('activeTab', 'detail');

                    setTimeout(() => {
                        window.dispatchEvent(new CustomEvent('chart:highlightDept', {
                            detail: {
                                deptId
                            }
                        }));
                        comp.call('showDetail', deptId);
                    }, 50);
                },


                update() {
                    // rebuild with toggles applied
                    this.buildOrUpdate();
                }
            }
        }
    </script>
@endpush
</div>
