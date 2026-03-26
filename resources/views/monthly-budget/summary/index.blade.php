@section('title', 'Monthly Budget Summary Reports')
@section('page-title', 'Monthly Budget Summary Reports')
@section('page-subtitle', 'Consolidated departmental budgets overview and management.')



<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 space-y-6" x-data="{}">

    {{-- Breadcrumbs --}}
    <nav aria-label="Breadcrumb" class="flex items-center text-xs text-slate-500 gap-1">
        <a href="{{ route('monthly-budget-summary.index') }}" class="hover:text-slate-700 hover:underline">
            Monthly Budget Summary Reports
        </a>
        <span>/</span>
        <span class="text-slate-700 font-medium">List</span>
    </nav>

    {{-- Spotlight Dashboard (Premium UI) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Reports --}}
        <div class="glass-card premium-shadow p-4 flex items-center gap-4 transition-all duration-300 hover:scale-[1.02] hover:shadow-indigo-100 group">
            <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-500 group-hover:bg-slate-200 transition-colors">
                <i class="bx bx-file text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Reports</p>
                <h4 class="text-xl font-bold text-slate-800">{{ number_format($stats['total']) }}</h4>
            </div>
        </div>

        {{-- Approved --}}
        <div class="glass-card premium-shadow p-4 flex items-center gap-4 transition-all duration-300 hover:scale-[1.02] hover:shadow-emerald-100 group">
            <div class="h-10 w-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-500 group-hover:bg-emerald-100 transition-colors">
                <i class="bx bx-check-double text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Approved</p>
                <h4 class="text-xl font-bold text-emerald-600">{{ number_format($stats['approved']) }}</h4>
            </div>
        </div>

        {{-- Pending --}}
        <div class="glass-card premium-shadow p-4 flex items-center gap-4 transition-all duration-300 hover:scale-[1.02] hover:shadow-amber-100 group">
            <div class="h-10 w-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 group-hover:bg-amber-100 transition-colors">
                <i class="bx bx-time text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">In Review</p>
                <h4 class="text-xl font-bold text-amber-600">{{ number_format($stats['pending']) }}</h4>
            </div>
        </div>

        {{-- Month Total --}}
        <div class="glass-card premium-shadow p-4 border-l-4 border-indigo-500 flex items-center gap-4 transition-all duration-300 hover:scale-[1.02] hover:shadow-indigo-100 group">
            <div class="h-10 w-10 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-500 group-hover:bg-indigo-100 transition-colors">
                <i class="bx bx-stats text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Month Total</p>
                <h4 class="text-xl font-bold text-indigo-600">Rp {{ number_format($stats['this_month_sum'], 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>

    {{-- Header + Controls --}}
    <div class="flex flex-col gap-4">

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="bx bx-list-ul text-indigo-500"></i>
                    Report Overview
                </h3>
            </div>
            
            {{-- Generate form (Livewire) --}}
            @if ($showGenerateButton)
                <div class="glass-card flex flex-col sm:flex-row sm:items-center gap-2 p-2 ring-1 ring-slate-200/50" x-data>
                    <div class="flex items-center gap-2 pl-1">
                        <div class="relative" x-data x-init="
                            if (window.flatpickr) {
                                window.flatpickr($refs.picker, {
                                    plugins: [new window.monthSelectPlugin({
                                        shorthand: true,
                                        dateFormat: 'm-Y',
                                        altFormat: 'F Y',
                                        theme: 'light'
                                    })],
                                    allowInput: true,
                                    onChange: (selectedDates, dateStr) => {
                                        $wire.set('generationMonth', dateStr);
                                    }
                                });
                            }
                        ">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                                <i class="bx bx-calendar text-slate-400 text-sm"></i>
                            </span>
                            <input x-ref="picker" type="text" 
                                class="block w-44 rounded-lg border border-slate-200 bg-white/50 pl-8 pr-3 py-1.5
                                       text-xs text-slate-800 focus:bg-white
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all placeholder:text-slate-400"
                                placeholder="Select Month..." aria-label="Select Month" required>
                        </div>
                    </div>

                    <button wire:click="prepareGeneration" wire:loading.attr="disabled"
                        class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-2
                                   text-xs font-bold text-white shadow-lg shadow-indigo-100 hover:bg-indigo-700 disabled:opacity-50 transition-all active:scale-95 group">
                        <i class="bx bx-plus-circle mr-2 text-[1rem] group-hover:rotate-90 transition-transform duration-300" wire:loading.remove wire:target="prepareGeneration"></i>
                        <span class="animate-spin h-3.5 w-3.5 border-2 border-white/30 border-t-white rounded-full mr-2"
                               wire:loading wire:target="prepareGeneration"></span>
                        Generate Summary
                    </button>
                </div>
            @endif
        </div>

        {{-- Filter toolbar --}}
        <div class="glass-card premium-shadow overflow-hidden">
            <div class="p-4">
                <div class="grid grid-cols-2 md:grid-cols-6 lg:grid-cols-7 gap-3 items-end">

                    {{-- Search --}}
                    <div class="col-span-2 md:col-span-2 lg:col-span-3">
                        <label for="search" class="block text-[11px] font-medium text-slate-600 mb-1">
                            Search
                        </label>
                        <input wire:model.live.debounce.400ms="search" type="text" id="search"
                            class="block w-full rounded-md border border-slate-300 bg-white px-3 py-1.5
                                   text-xs text-slate-800 shadow-sm
                                   focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Doc # or Creator name">
                    </div>

                    {{-- Month from --}}
                    <div class="col-span-1 md:col-span-1" x-data x-init="
                        if (window.flatpickr) {
                            window.flatpickr($refs.picker, {
                                plugins: [new window.monthSelectPlugin({
                                    shorthand: true,
                                    dateFormat: 'm-Y',
                                    altFormat: 'F Y',
                                    theme: 'light'
                                })],
                                allowInput: true,
                                onChange: (selectedDates, dateStr) => {
                                    $wire.set('monthFrom', dateStr);
                                }
                            });
                        }
                    " @clear-filters.window="if(window.flatpickr) $refs.picker._flatpickr.clear()">
                        <label for="from" class="block text-[11px] font-medium text-slate-600 mb-1">
                            Month from
                        </label>
                        <input x-ref="picker" type="text" id="from"
                            class="block w-full rounded-md border border-slate-300 bg-white px-3 py-1.5
                                   text-xs text-slate-800 shadow-sm
                                   focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="mm-yyyy">
                    </div>

                    {{-- Month to --}}
                    <div class="col-span-1 md:col-span-1" x-data x-init="
                        if (window.flatpickr) {
                            window.flatpickr($refs.picker, {
                                plugins: [new window.monthSelectPlugin({
                                    shorthand: true,
                                    dateFormat: 'm-Y',
                                    altFormat: 'F Y',
                                    theme: 'light'
                                })],
                                allowInput: true,
                                onChange: (selectedDates, dateStr) => {
                                    $wire.set('monthTo', dateStr);
                                }
                            });
                        }
                    " @clear-filters.window="if(window.flatpickr) $refs.picker._flatpickr.clear()">
                        <label for="to" class="block text-[11px] font-medium text-slate-600 mb-1">
                            Month to
                        </label>
                        <input x-ref="picker" type="text" id="to"
                            class="block w-full rounded-md border border-slate-300 bg-white px-3 py-1.5
                                   text-xs text-slate-800 shadow-sm
                                   focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="mm-yyyy">
                    </div>

                    {{-- Status --}}
                    <div class="col-span-1 md:col-span-1">
                        <label for="status" class="block text-[11px] font-medium text-slate-600 mb-1">
                            Status
                        </label>
                        <select wire:model.live="status" id="status"
                            class="block w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5
                                   text-xs text-slate-800 shadow-sm
                                   focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Status</option>
                            <option value="DRAFT">Draft</option>
                            <option value="IN_REVIEW">In Review</option>
                            <option value="APPROVED">Approved</option>
                            <option value="REJECTED">Rejected</option>
                            <option value="RETURNED">Returned</option>
                            <option value="CANCELED">Canceled</option>
                        </select>
                    </div>

                    {{-- Per page --}}
                    <div class="col-span-1 md:col-span-1">
                        <label for="perPage" class="block text-[11px] font-medium text-slate-600 mb-1">
                            Per page
                        </label>
                        <select wire:model.live="perPage" id="perPage"
                            class="block w-full rounded-md border border-slate-300 bg-white px-2.5 py-1.5
                                   text-xs text-slate-800 shadow-sm
                                   focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            <option>10</option>
                            <option>15</option>
                            <option>25</option>
                            <option>50</option>
                        </select>
                    </div>

                    {{-- Clear --}}
                    <div class="col-span-1 md:col-span-1 lg:col-span-1">
                        <button wire:click="clearFilters" type="button"
                            class="inline-flex w-full items-center justify-center rounded-md border
                                   border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700
                                   shadow-sm hover:bg-slate-50">
                            <i class="bx bx-filter-alt-off mr-1 text-[0.9rem]"></i>
                            Clear
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- List --}}
    <div class="glass-card premium-shadow relative overflow-hidden">

        {{-- Loading overlay --}}
        <div wire:loading.flex
            class="absolute inset-0 bg-white/70 backdrop-blur-[1px] z-20 flex items-center justify-center
                   text-xs text-slate-600"
            aria-live="polite">
            <div class="inline-flex items-center gap-2">
                <div class="h-4 w-4 border-2 border-slate-300 border-t-indigo-500 rounded-full animate-spin"></div>
                <span>Loading…</span>
            </div>
        </div>

        <div class="p-0">
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs text-slate-800">
                    <thead class="bg-slate-50/80 backdrop-blur-md sticky top-0 z-10">
                        <tr>
                            <th
                                class="px-3 py-3 text-left text-[11px] font-bold
                                       text-slate-500 uppercase whitespace-nowrap tracking-wider">
                                #
                            </th>

                            {{-- Doc Number --}}
                            <th class="px-3 py-3 text-left">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold
                                               text-slate-600 uppercase hover:text-slate-900 tracking-wider"
                                    wire:click="sortBy('doc_num')">
                                    <span>Doc. Number</span>
                                    @include('partials.sort-icon', [
                                        'field' => 'doc_num',
                                        'sortField' => $sortField,
                                        'direction' => $sortDirection,
                                    ])
                                </button>
                            </th>

                            {{-- Report Month --}}
                            <th class="px-3 py-3 text-left">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold
                                               text-slate-600 uppercase hover:text-slate-900 tracking-wider"
                                    wire:click="sortBy('report_date')">
                                    <span>Report Month</span>
                                    @include('partials.sort-icon', [
                                        'field' => 'report_date',
                                        'sortField' => $sortField,
                                        'direction' => $sortDirection,
                                    ])
                                </button>
                            </th>

                            {{-- Created At --}}
                            <th class="px-3 py-3 text-left">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold
                                               text-slate-600 uppercase hover:text-slate-900 tracking-wider"
                                    wire:click="sortBy('created_at')">
                                    <span>Created At</span>
                                    @include('partials.sort-icon', [
                                        'field' => 'created_at',
                                        'sortField' => $sortField,
                                        'direction' => $sortDirection,
                                    ])
                                </button>
                            </th>

                            {{-- Status --}}
                            <th
                                class="px-3 py-3 text-left text-[11px] font-bold
                                       text-slate-500 uppercase tracking-wider">
                                Status
                            </th>

                            {{-- Total --}}
                            <th class="px-3 py-3 text-right">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-[11px] font-bold
                                               text-slate-600 uppercase hover:text-slate-900 tracking-wider"
                                    wire:click="sortBy('total')">
                                    <span>Total</span>
                                    @include('partials.sort-icon', [
                                        'field' => 'total',
                                        'sortField' => $sortField,
                                        'direction' => $sortDirection,
                                    ])
                                </button>
                            </th>

                            {{-- MoM --}}
                            <th
                                class="px-3 py-3 text-right text-[11px] font-bold
                                       text-slate-500 uppercase whitespace-nowrap tracking-wider">
                                MoM
                            </th>

                            {{-- Action --}}
                            <th
                                class="px-3 py-3 text-left text-[11px] font-bold
                                       text-slate-500 uppercase tracking-wider">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse ($reports as $report)
                            @php
                                $reportDate = \Carbon\Carbon::parse($report->report_date);
                                $monthYear = $reportDate->format('F Y');
                                $createdAt = \Carbon\Carbon::parse($report->created_at);
                                $formattedCreatedAt = $createdAt->format('d/m/Y (H:i:s)');
                                $total = (float) ($report->total_amount ?? 0);
                                $m = $report->mom ?? [
                                    'has_prev' => false,
                                    'direction' => 'none',
                                    'diff' => null,
                                    'pct' => null,
                                    'prev' => 0,
                                ];
                            @endphp

                            <tr class="hover:bg-slate-50/80 transition-all duration-200 group/row cursor-default">
                                {{-- # --}}
                                <td class="px-3 py-2 whitespace-nowrap text-[11px] text-slate-500">
                                    {{ $reports->firstItem() + $loop->index }}
                                </td>

                                {{-- Doc num --}}
                                <td class="px-3 py-2 whitespace-nowrap text-xs">
                                    <span class="font-semibold text-slate-800">
                                        {{ $report->doc_num }}
                                    </span>
                                </td>

                                {{-- Report month --}}
                                <td class="px-3 py-2 whitespace-nowrap text-xs text-slate-700">
                                    <time datetime="{{ $reportDate->format('Y-m-01') }}"
                                        title="{{ $reportDate->toFormattedDayDateString() }}">
                                        {{ $monthYear }}
                                    </time>
                                </td>

                                {{-- Created at --}}
                                <td class="px-3 py-2 whitespace-nowrap text-[11px] text-slate-600">
                                    <time datetime="{{ $createdAt->toIso8601String() }}"
                                        title="{{ $createdAt->toDayDateTimeString() }}">
                                        {{ $formattedCreatedAt }}
                                    </time>
                                </td>

                                {{-- Status (partial masih Bootstrap; bisa di-Tailwind-kan nanti) --}}
                                <td class="px-3 py-2 whitespace-nowrap text-xs">
                                    {{-- Use unified status badge --}}
                                    @include('partials.workflow-status-badge', ['record' => $report])
                                </td>

                                {{-- Total --}}
                                <td class="px-3 py-2 whitespace-nowrap text-right text-xs">
                                    <span class="font-semibold text-slate-800"
                                        title="Total quantity × cost per unit">
                                        Rp {{ number_format($total, 0, ',', '.') }}
                                    </span>
                                </td>

                                {{-- MoM --}}
                                <td class="px-3 py-2 whitespace-nowrap text-right text-xs">
                                    @if (!$m['has_prev'])
                                        <span class="text-slate-400" title="No prior month">
                                            —
                                        </span>
                                    @elseif ($m['direction'] === 'up')
                                        <span
                                            class="inline-flex items-center rounded-full border border-emerald-100
                                                   bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700"
                                            title="Prev: Rp {{ number_format($m['prev'], 0, ',', '.') }}">
                                            <i class="bx bx-trending-up mr-1 text-[0.9rem]"></i>
                                            Rp {{ number_format($m['diff'], 0, ',', '.') }}
                                            <span class="ml-1">
                                                ({{ number_format($m['pct'], 2, ',', '.') }}%)
                                            </span>
                                        </span>
                                    @elseif ($m['direction'] === 'down')
                                        <span
                                            class="inline-flex items-center rounded-full border border-rose-100
                                                   bg-rose-50 px-2.5 py-1 text-[11px] font-medium text-rose-700"
                                            title="Prev: Rp {{ number_format($m['prev'], 0, ',', '.') }}">
                                            <i class="bx bx-trending-down mr-1 text-[0.9rem]"></i>
                                            Rp {{ number_format($m['diff'], 0, ',', '.') }}
                                            <span class="ml-1">
                                                ({{ number_format($m['pct'], 2, ',', '.') }}%)
                                            </span>
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full border border-slate-200
                                                   bg-slate-50 px-2.5 py-1 text-[11px] font-medium text-slate-600"
                                            title="Prev: Rp {{ number_format($m['prev'], 0, ',', '.') }}">
                                            <i class="bx bx-minus mr-1 text-[0.9rem]"></i>
                                            0 (0%)
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-3 py-2 whitespace-nowrap text-xs">
                                    <div class="flex flex-wrap gap-2 justify-start items-center">
                                        <a wire:navigate
                                            href="{{ route('monthly-budget-summary.show', $report->id) }}"
                                            class="inline-flex items-center rounded-lg border border-slate-200
                                                   bg-white px-3 py-1.5 text-[11px] font-bold text-slate-700
                                                   shadow-sm hover:bg-slate-50 hover:border-slate-300 hover:shadow transition-all active:scale-95 group/btn">
                                            <i class="bx bx-show mr-1.5 text-[1rem] text-slate-400 group-hover/btn:text-indigo-500 transition-colors"></i>
                                            Detail
                                        </a>

                                        @if ($authUser->id == $report->creator_id)
                                            @if ($report->workflow_status === 'DRAFT')
                                                @include('partials.delete-confirmation-modal', [
                                                    'id' => $report->id,
                                                    'route' => 'monthly-budget-summary.delete',
                                                    'title' => 'Delete report confirmation',
                                                    'body' => "Are you sure want to delete report <strong>{$report->doc_num}</strong>?",
                                                ])
                                            @elseif ($report->workflow_status === 'IN_REVIEW' || $report->workflow_status === 'RETURNED')
                                                @include('partials.cancel-modal', [
                                                    'id' => $report->id,
                                                    'route' => 'monthly-budget-summary.cancel',
                                                    'title' => "Cancel Summary: <strong>{$report->doc_num}</strong>",
                                                    'iconOnly' => true
                                                ])
                                            @endif
                                        @endif

                                        <button wire:click="cloneReport({{ $report->id }})"
                                            wire:confirm="Are you sure you want to clone this report to the next month? All line items will be copied."
                                            class="inline-flex items-center rounded-lg border border-indigo-100
                                                   bg-indigo-50/50 px-3 py-1.5 text-[11px] font-bold text-indigo-700
                                                   shadow-sm hover:bg-indigo-100/80 transition-all active:scale-95 group/clone"
                                            title="Clone to Next Month">
                                            <i class="bx bx-duplicate mr-1.5 text-[1rem] text-indigo-400 group-hover/clone:rotate-12 transition-transform"></i>
                                            Clone
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12">
                                    <div class="flex flex-col items-center text-center max-w-sm mx-auto">
                                        <div class="h-16 w-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 ring-1 ring-slate-100">
                                            <i class="bx bx-file-find text-4xl text-slate-300"></i>
                                        </div>
                                        <h4 class="font-bold text-slate-700 mb-1">No reports found</h4>
                                        <p class="text-[11px] text-slate-500 mb-6 leading-relaxed">
                                            We couldn't find any summary reports matching your criteria. Try adjusting your filters or generate a new summary.
                                        </p>
                                        @if ($showGenerateButton)
                                            <a href="#monthPicker"
                                                class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2
                                                      text-xs font-bold text-white shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                                                <i class="bx bx-plus-circle mr-2 text-[1rem]"></i>
                                                Generate First Summary
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="border-t border-slate-100 px-4 py-3 bg-slate-50/30">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-3 text-[11px] text-slate-500">
                    <p>
                        Showing <span class="font-bold text-slate-700">{{ $reports->firstItem() ?? 0 }}</span>
                        to <span class="font-bold text-slate-700">{{ $reports->lastItem() ?? 0 }}</span>
                        of <span class="font-bold text-slate-700">{{ $reports->total() }}</span> reports
                    </p>
                    <span class="h-1 w-1 rounded-full bg-slate-300"></span>
                    <p>
                        Page <span class="font-bold text-slate-700">{{ $reports->currentPage() }}</span>
                        of <span class="font-bold text-slate-700">{{ $reports->lastPage() }}</span>
                    </p>
                </div>
                <div class="text-xs">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>
    {{-- Confirmation Modal --}}
    <x-modal wire:model="isConfirmingGeneration" maxWidth="2xl">
        <div class="bg-white px-8 pt-8 pb-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl bg-indigo-50 sm:mx-0 sm:h-12 sm:w-12">
                    <i class="bx bx-file text-2xl text-indigo-600"></i>
                </div>
                <div class="mt-4 text-center sm:mt-0 sm:ml-5 sm:text-left w-full">
                    <h3 class="text-xl font-extrabold leading-tight text-slate-900" id="modal-title">
                        Confirm Summary Generation
                    </h3>
                    <div class="mt-2">
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Generating summary for <span class="font-bold text-slate-900 bg-slate-100 px-1.5 py-0.5 rounded">{{ $generationMonth }}</span>. This will consolidate all currently <span class="text-emerald-600 font-bold uppercase tracking-tight">Approved</span> departmental reports into a single summary.
                        </p>
                    </div>

                    {{-- Preview Table --}}
                    <div class="mt-8 overflow-hidden rounded-2xl border border-slate-200 premium-shadow">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50/50">
                                <tr>
                                    <th scope="col" class="px-5 py-3 text-left text-[11px] font-bold text-slate-500 uppercase tracking-widest">Department</th>
                                    <th scope="col" class="px-5 py-3 text-center text-[11px] font-bold text-slate-500 uppercase tracking-widest">MBR Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @php
                                    $allApproved = true;
                                @endphp
                                @foreach($generationPreview as $item)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="whitespace-nowrap px-5 py-3 text-xs text-slate-700 font-bold">
                                            {{ $item['name'] }}
                                        </td>
                                        <td class="whitespace-nowrap px-5 py-3 text-center text-xs">
                                            @if($item['status'] === 'APPROVED')
                                                <span class="inline-flex items-center rounded-lg bg-emerald-50 px-2.5 py-1 text-[10px] font-bold text-emerald-700 border border-emerald-100/50">
                                                    <i class="bx bxs-check-circle mr-1.5"></i> Approved
                                                </span>
                                            @elseif($item['status'] === 'MISSING')
                                                @php $allApproved = false; @endphp
                                                <span class="inline-flex items-center rounded-lg bg-rose-50 px-2.5 py-1 text-[10px] font-bold text-rose-700 border border-rose-100/50">
                                                    <i class="bx bxs-error-circle mr-1.5"></i> Missing
                                                </span>
                                            @else
                                                @php $allApproved = false; @endphp
                                                <span class="inline-flex items-center rounded-lg bg-amber-50 px-2.5 py-1 text-[10px] font-bold text-amber-700 border border-amber-100/50">
                                                    <i class="bx bxs-time mr-1.5"></i> {{ $item['status'] }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(!$allApproved)
                        <div class="mt-4 rounded-lg bg-amber-50 p-3 border border-amber-200">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="bx bxs-info-circle text-amber-400"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-[11px] font-medium text-amber-800 leading-normal">
                                        Warning: Some non-office departments have not yet approved their monthly budget reports. 
                                        If you proceed, these departments will not be included in the summary totals.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
        <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3">
            <button wire:click="generateConfirmed" type="button" 
                    class="inline-flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 sm:w-auto transition-all duration-200 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                Confirm & Generate
            </button>
            <button wire:click="$set('isConfirmingGeneration', false)" type="button" 
                    class="inline-flex w-full justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm border border-slate-300 hover:bg-slate-50 sm:w-auto transition-all duration-200">
                Cancel
            </button>
        </div>
    </x-modal>
</div>
