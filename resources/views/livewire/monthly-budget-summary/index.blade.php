@push('scripts')
    <script type="module">
        // Month pickers (bootstrap-datepicker or similar should be globally loaded)
        const monthOpts = {
            format: "mm-yyyy",
            startView: "months",
            minViewMode: "months",
            autoclose: true,
        };

        $('#monthPicker').datepicker(monthOpts);
        $('#from').datepicker(monthOpts).on('changeDate', e => @this.set('monthFrom', e.format()));
        $('#to').datepicker(monthOpts).on('changeDate', e => @this.set('monthTo', e.format()));

        // Tooltips
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].forEach(el => new bootstrap.Tooltip(el, {
            container: 'body'
        }));
    </script>
@endpush

@push('head')
    <style>
        thead tr>* {
            z-index: 2;
        }
    </style>
@endpush

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4 space-y-4">
    {{-- Breadcrumbs --}}
    <nav aria-label="Breadcrumb" class="flex items-center text-xs text-slate-500 gap-1">
        <a href="{{ route('monthly-budget-summary-report.index') }}" class="hover:text-slate-700 hover:underline">
            Monthly Budget Summary Reports
        </a>
        <span>/</span>
        <span class="text-slate-700 font-medium">List</span>
    </nav>

    {{-- Header + Controls --}}
    <div class="flex flex-col gap-3">

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    Monthly Budget Summary Reports
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Showing <span class="font-semibold text-slate-700">{{ $reports->count() }}</span>
                    of
                    <span class="font-semibold text-slate-700">{{ $reports->total() }}</span>
                    records
                </p>
            </div>

            {{-- Generate form (server POST) --}}
            @if ($showGenerateButton)
                <form action="{{ route('monthly.budget.summary.report.store') }}" method="post"
                    class="flex flex-col sm:flex-row sm:items-center gap-2" x-data
                    x-on:submit="$el.querySelector('button[type=submit]').disabled = true">
                    @csrf

                    <input type="hidden" name="created_autograph" value="{{ ucwords(auth()->user()->name) }}">

                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                                <i class="bx bx-calendar text-slate-400 text-sm"></i>
                            </span>
                            <input type="text" id="monthPicker" name="month"
                                class="block w-44 rounded-md border border-slate-300 bg-white pl-8 pr-3 py-1.5
                                       text-xs text-slate-800 shadow-sm
                                       focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Select Month (mm-yyyy)" aria-label="Select Month" required>
                        </div>
                    </div>

                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-1.5
                                   text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                        <i class="bx bx-refresh mr-1 text-[0.9rem]"></i>
                        Generate
                    </button>
                </form>
            @endif
        </div>

        {{-- Filter toolbar --}}
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm">
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
                    <div class="col-span-1 md:col-span-1">
                        <label for="from" class="block text-[11px] font-medium text-slate-600 mb-1">
                            Month from
                        </label>
                        <input wire:model.live="monthFrom" type="text" id="from"
                            class="block w-full rounded-md border border-slate-300 bg-white px-3 py-1.5
                                   text-xs text-slate-800 shadow-sm
                                   focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="mm-yyyy">
                    </div>

                    {{-- Month to --}}
                    <div class="col-span-1 md:col-span-1">
                        <label for="to" class="block text-[11px] font-medium text-slate-600 mb-1">
                            Month to
                        </label>
                        <input wire:model.live="monthTo" type="text" id="to"
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
                            <option value="">All</option>
                            <option value="1">Draft</option>
                            <option value="2">Submitted</option>
                            <option value="3">Design Head Approved</option>
                            <option value="4">GM Approved</option>
                            <option value="5">Director Review</option>
                            <option value="6">Director Approved</option>
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
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm relative overflow-hidden">

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
                    <thead class="bg-slate-50">
                        <tr>
                            <th
                                class="sticky top-0 z-10 bg-slate-50 px-3 py-2 text-left text-[11px] font-semibold
                                       text-slate-500 uppercase whitespace-nowrap">
                                #
                            </th>

                            {{-- Doc Number --}}
                            <th class="sticky top-0 z-10 bg-slate-50 px-3 py-2 text-left">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-[11px] font-semibold
                                               text-slate-600 uppercase hover:text-slate-900"
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
                            <th class="sticky top-0 z-10 bg-slate-50 px-3 py-2 text-left">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-[11px] font-semibold
                                               text-slate-600 uppercase hover:text-slate-900"
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
                            <th class="sticky top-0 z-10 bg-slate-50 px-3 py-2 text-left">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-[11px] font-semibold
                                               text-slate-600 uppercase hover:text-slate-900"
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
                                class="sticky top-0 z-10 bg-slate-50 px-3 py-2 text-left text-[11px] font-semibold
                                       text-slate-500 uppercase">
                                Status
                            </th>

                            {{-- Total --}}
                            <th class="sticky top-0 z-10 bg-slate-50 px-3 py-2 text-right">
                                <button type="button"
                                    class="inline-flex items-center gap-1 text-[11px] font-semibold
                                               text-slate-600 uppercase hover:text-slate-900"
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
                                class="sticky top-0 z-10 bg-slate-50 px-3 py-2 text-right text-[11px] font-semibold
                                       text-slate-500 uppercase whitespace-nowrap">
                                MoM
                            </th>

                            {{-- Action --}}
                            <th
                                class="sticky top-0 z-10 bg-slate-50 px-3 py-2 text-left text-[11px] font-semibold
                                       text-slate-500 uppercase">
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

                            <tr class="hover:bg-slate-50/60">
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
                                    @include('partials.monthly-budget-summary-report-status', [
                                        'status' => $report->status,
                                        'report' => $report,
                                    ])
                                </td>

                                {{-- Total --}}
                                <td class="px-3 py-2 whitespace-nowrap text-right text-xs">
                                    <span class="font-semibold text-slate-800 cursor-help" data-bs-toggle="tooltip"
                                        data-bs-title="Total quantity × cost per unit">
                                        Rp {{ number_format($total, 0, ',', '.') }}
                                    </span>
                                </td>

                                {{-- MoM --}}
                                <td class="px-3 py-2 whitespace-nowrap text-right text-xs">
                                    @if (!$m['has_prev'])
                                        <span class="text-slate-400" data-bs-toggle="tooltip"
                                            data-bs-title="No prior month">
                                            —
                                        </span>
                                    @elseif ($m['direction'] === 'up')
                                        <span
                                            class="inline-flex items-center rounded-full border border-emerald-100
                                                   bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700"
                                            data-bs-toggle="tooltip"
                                            data-bs-title="Prev: Rp {{ number_format($m['prev'], 0, ',', '.') }}">
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
                                            data-bs-toggle="tooltip"
                                            data-bs-title="Prev: Rp {{ number_format($m['prev'], 0, ',', '.') }}">
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
                                            data-bs-toggle="tooltip"
                                            data-bs-title="Prev: Rp {{ number_format($m['prev'], 0, ',', '.') }}">
                                            <i class="bx bx-minus mr-1 text-[0.9rem]"></i>
                                            0 (0%)
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-3 py-2 whitespace-nowrap text-xs">
                                    <div class="flex flex-wrap gap-1 justify-start">
                                        <a wire:navigate
                                            href="{{ route('monthly.budget.summary.report.show', $report->id) }}"
                                            class="inline-flex items-center rounded-md border border-slate-300
                                                   bg-white px-2.5 py-1 text-[11px] font-medium text-slate-700
                                                   shadow-sm hover:bg-slate-50">
                                            <i class="bx bx-info-circle mr-1 text-[0.9rem]"></i>
                                            Detail
                                        </a>



                                        @if ($authUser->id == $report->creator_id)
                                            @if (!$report->status === 1)
                                                @include('partials.delete-confirmation-modal', [
                                                    'id' => $report->id,
                                                    'route' => 'monthly.budget.summary.report.delete',
                                                    'title' => 'Delete report confirmation',
                                                    'body' => "Are you sure want to delete report <strong>$report->doc_num</strong>?",
                                                ])
                                            @elseif (!in_array($report->status, [2, 3, 4], true))
                                                @include('partials.cancel-confirmation-modal', [
                                                    'id' => $report->id,
                                                    'route' => route(
                                                        'monthly.budget.summary.report.cancel',
                                                        $report->id),
                                                ])
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-8">
                                    <div class="flex flex-col items-center text-center text-xs text-slate-500">
                                        <i class="bx bx-file-find text-3xl text-slate-300 mb-2"></i>
                                        <p class="font-semibold text-slate-700">No reports found</p>
                                        <p class="mt-1 mb-3 text-[11px] text-slate-500">
                                            Adjust filters or generate a new summary report.
                                        </p>
                                        @if ($showGenerateButton)
                                            <a href="#monthPicker"
                                                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5
                                                      text-xs font-semibold text-white shadow-sm hover:bg-indigo-700">
                                                <i class="bx bx-refresh mr-1 text-[0.9rem]"></i>
                                                Generate Monthly Report
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
        <div class="border-t border-slate-100 px-4 py-3">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <p class="text-[11px] text-slate-500">
                    Page
                    <span class="font-semibold text-slate-700">{{ $reports->currentPage() }}</span>
                    of
                    <span class="font-semibold text-slate-700">{{ $reports->lastPage() }}</span>
                </p>
                <div class="text-xs">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
