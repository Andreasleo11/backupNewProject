<div class="space-y-3">

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div class="text-xs sm:text-sm text-slate-600">
            <span class="text-slate-500">Department:</span>
            <span class="font-semibold text-slate-800">
                {{ $deptName !== '' ? $deptName : $deptId }}
            </span>
            <span class="mx-1 text-slate-400">•</span>
            <span class="text-slate-500">Period:</span>
            <span class="font-semibold text-slate-800">
                {{ $monthLabel !== '' ? $monthLabel : \Illuminate\Support\Carbon::parse($month . '-01')->isoFormat('MMMM YYYY') }}
            </span>
            <span class="mx-1 text-slate-400">•</span>
            <span class="text-slate-500">Approvers:</span>
            <span class="font-semibold text-slate-800">
                {{ $prSigner ?: 'All' }}
            </span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            {{-- Search --}}
            <div class="w-64">
                <div
                    class="flex items-center rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs sm:text-sm text-slate-700 shadow-sm">
                    <span class="mr-2 text-slate-400">
                        <i class="bi bi-search"></i>
                    </span>
                    <input
                        type="search"
                        class="flex-1 bg-transparent text-xs sm:text-sm focus:outline-none"
                        placeholder="Search item/source/uom…"
                        wire:model.live.debounce.300ms="search"
                    >
                </div>
            </div>

            {{-- Per page --}}
            <select
                class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs sm:text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                wire:model.live="perPage"
            >
                <option value="10">10 / page</option>
                <option value="25">25 / page</option>
                <option value="50">50 / page</option>
                <option value="100">100 / page</option>
            </select>
        </div>
    </div>

    {{-- Badges --}}
    <div class="flex flex-wrap items-center gap-2 text-xs sm:text-[13px]">
        <span
            class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-slate-700">
            <i class="bi bi-list-check mr-1 text-slate-400"></i>
            Lines:
            <span class="ml-1 font-semibold">{{ $rows->total() }}</span>
        </span>
        <span
            class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-slate-700">
            <i class="bi bi-cash-coin mr-1 text-emerald-500"></i>
            Total:
            <span class="ml-1 font-semibold">
                Rp {{ number_format($sumTotal, 2, ',', '.') }}
            </span>
        </span>
    </div>

    {{-- Table --}}
    <div class="detail-table-wrapper rounded-lg border border-slate-200 bg-white">
        <table class="table-sticky min-w-full text-left text-sm text-slate-700">
            <thead>
                @php
                    $arrow = fn($col) => $sortBy === $col
                        ? ($sortDir === 'asc' ? 'bi-caret-up-fill' : 'bi-caret-down-fill')
                        : 'bi-filter';
                @endphp
                <tr class="border-b border-slate-200 bg-slate-50/80 text-xs uppercase text-slate-500">
                    <th
                        class="sortable px-3 py-2 font-medium"
                        style="min-width: 112px;"
                        wire:click="sort('expense_date')"
                    >
                        Date
                        <i class="bi {{ $arrow('expense_date') }} ml-1"></i>
                    </th>
                    <th
                        class="sortable px-3 py-2 font-medium"
                        style="min-width: 128px;"
                        wire:click="sort('source')"
                    >
                        Source
                        <i class="bi {{ $arrow('source') }} ml-1"></i>
                    </th>
                    <th
                        class="sortable px-3 py-2 font-medium"
                        style="min-width: 260px;"
                        wire:click="sort('item_name')"
                    >
                        Item
                        <i class="bi {{ $arrow('item_name') }} ml-1"></i>
                    </th>
                    <th
                        class="sortable px-3 py-2 text-right font-medium"
                        style="min-width: 100px;"
                        wire:click="sort('quantity')"
                    >
                        Qty
                        <i class="bi {{ $arrow('quantity') }} ml-1"></i>
                    </th>
                    <th
                        class="sortable px-3 py-2 font-medium"
                        style="min-width: 80px;"
                        wire:click="sort('uom')"
                    >
                        UoM
                        <i class="bi {{ $arrow('uom') }} ml-1"></i>
                    </th>
                    <th
                        class="sortable px-3 py-2 text-right font-medium"
                        style="min-width: 140px;"
                        wire:click="sort('unit_price')"
                    >
                        Unit Price
                        <i class="bi {{ $arrow('unit_price') }} ml-1"></i>
                    </th>
                    <th
                        class="sortable px-3 py-2 text-right font-medium"
                        style="min-width: 160px;"
                        wire:click="sort('line_total')"
                    >
                        Line Total
                        <i class="bi {{ $arrow('line_total') }} ml-1"></i>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($rows as $l)
                    @php
                        $isPR   = $l->source === 'purchase_request';
                        $label  = $isPR ? 'Purchase Request' : 'Monthly Budget';
                        $pillClasses = $isPR
                            ? 'bg-indigo-50 text-indigo-700 ring-indigo-100'
                            : 'bg-emerald-50 text-emerald-700 ring-emerald-100';

                        if ($isPR) {
                            $url = \Illuminate\Support\Facades\Route::has('purchase-requests.show')
                                ? route('purchase-requests.show', $l->doc_id)
                                : url('/purchase-requests/' . $l->doc_id);
                        } else {
                            $url = \Illuminate\Support\Facades\Route::has('monthly.budget.summary.report.show')
                                ? route('monthly.budget.summary.report.show', $l->doc_id)
                                : url('monthlyBudgetSummaryReport/' . $l->doc_id);
                        }
                    @endphp
                    <tr class="hover:bg-indigo-50/40">
                        <td class="whitespace-nowrap px-3 py-2 text-xs sm:text-sm">
                            {{ \Illuminate\Support\Carbon::parse($l->expense_date)->format('Y-m-d') }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-xs sm:text-sm">
                            <span
                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium ring-1 {{ $pillClasses }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-xs sm:text-sm">
                            <a
                                href="{{ $url }}"
                                target="_blank"
                                rel="noopener"
                                class="cell-clip text-indigo-600 underline-offset-2 hover:text-indigo-800 hover:underline"
                                title="{{ $l->item_name }}"
                            >
                                {{ $l->item_name }}
                            </a>
                        </td>
                        <td class="num px-3 py-2 text-right text-xs sm:text-sm">
                            {{ number_format($l->quantity, 2, ',', '.') }}
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-xs sm:text-sm">
                            {{ $l->uom }}
                        </td>
                        <td class="num px-3 py-2 text-right text-xs sm:text-sm">
                            Rp {{ number_format($l->unit_price, 2, ',', '.') }}
                        </td>
                        <td class="num px-3 py-2 text-right text-xs sm:text-sm font-semibold">
                            Rp {{ number_format($l->line_total, 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-6 text-center text-xs sm:text-sm text-slate-400">
                            <i class="bi bi-inbox mr-1"></i>
                            No lines found.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if ($rows->total())
                <tfoot>
                    <tr class="border-t border-slate-200 bg-slate-50/90 text-sm">
                        <th colspan="3" class="px-3 py-2 text-right font-medium text-slate-700">
                            Totals
                        </th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th class="num px-3 py-2 text-right font-semibold text-slate-900">
                            Rp {{ number_format($sumTotal, 2, ',', '.') }}
                        </th>
                    </tr>
                @endif
        </table>
    </div>

    {{-- Pagination + meta --}}
    @php
        $from  = $rows->firstItem() ?? 0;
        $to    = $rows->lastItem() ?? 0;
        $total = $rows->total();
    @endphp

    <div class="flex flex-col items-start justify-between gap-2 pt-1 text-xs sm:flex-row sm:items-center">
        <div class="text-slate-500">
            Showing {{ $from }} to {{ $to }} of {{ $total }} results
        </div>
        <div class="self-end">
            {{-- kalau mau full Tailwind pagination, ganti view di sini --}}
            {{ $rows->links('vendor.livewire.bootstrap-noscroll') }}
        </div>
    </div>
</div>

@pushOnce('head')
    <style>
        .detail-table-wrapper {
            max-height: 56vh;
            overflow: auto;
        }

        .table-sticky thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #ffffff;
            box-shadow: 0 1px 0 rgba(15, 23, 42, 0.06);
        }

        .num {
            font-variant-numeric: tabular-nums;
        }

        .cell-clip {
            max-width: 520px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sortable {
            cursor: pointer;
            user-select: none;
        }
    </style>
@endPushOnce
