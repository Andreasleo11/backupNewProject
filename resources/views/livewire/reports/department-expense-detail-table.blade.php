<div>
    {{-- Toolbar --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div class="small">
            <span class="text-muted">Department:</span>
            <span class="fw-semibold">{{ $deptName !== '' ? $deptName : $deptId }}</span>
            <span class="text-muted"> • Period:</span>
            <span class="fw-semibold">
                {{ $monthLabel !== '' ? $monthLabel : \Illuminate\Support\Carbon::parse($month . '-01')->isoFormat('MMMM YYYY') }}
            </span>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="input-group input-group-sm" style="width: 260px;">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input class="form-control" type="search" placeholder="Search item/source/uom…"
                    wire:model.live.debounce.300ms="search">
            </div>

            <select class="form-select form-select-sm" style="width: 110px;" wire:model.live="perPage">
                <option value="10">10 / page</option>
                <option value="25">25 / page</option>
                <option value="50">50 / page</option>
                <option value="100">100 / page</option>
            </select>
        </div>
    </div>

    {{-- Badges --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <span class="badge rounded-pill text-bg-light border">
            <i class="bi bi-list-check me-1"></i> Lines:
            <span class="fw-semibold ms-1">{{ $rows->total() }}</span>
        </span>
        <span class="badge rounded-pill text-bg-light border">
            <i class="bi bi-basket2 me-1"></i> Qty:
            <span class="fw-semibold ms-1">{{ number_format($sumQty, 2, ',', '.') }}</span>
        </span>
        <span class="badge rounded-pill text-bg-light border">
            <i class="bi bi-cash-coin me-1"></i> Total:
            <span class="fw-semibold ms-1">Rp {{ number_format($sumTotal, 2, ',', '.') }}</span>
        </span>
    </div>

    {{-- Table --}}
    <div class="detail-table-wrapper">
        <table class="table table-striped table-hover align-middle mb-0 table-sticky">
            <thead class="table-light">
                <tr>
                    @php
                        $arrow = fn($col) => $sortBy === $col
                            ? ($sortDir === 'asc'
                                ? 'bi-caret-up-fill'
                                : 'bi-caret-down-fill')
                            : 'bi-filter';
                    @endphp
                    <th style="min-width: 112px;" class="sortable" wire:click="sort('expense_date')">
                        Date <i class="bi {{ $arrow('expense_date') }} ms-1 small"></i>
                    </th>
                    <th style="min-width: 128px;" class="sortable" wire:click="sort('source')">
                        Source <i class="bi {{ $arrow('source') }} ms-1 small"></i>
                    </th>
                    <th style="min-width: 260px;" class="sortable" wire:click="sort('item_name')">
                        Item <i class="bi {{ $arrow('item_name') }} ms-1 small"></i>
                    </th>
                    <th class="text-end sortable" style="min-width: 100px;" wire:click="sort('quantity')">
                        Qty <i class="bi {{ $arrow('quantity') }} ms-1 small"></i>
                    </th>
                    <th style="min-width: 80px;" class="sortable" wire:click="sort('uom')">
                        UoM <i class="bi {{ $arrow('uom') }} ms-1 small"></i>
                    </th>
                    <th class="text-end sortable" style="min-width: 140px;" wire:click="sort('unit_price')">
                        Unit Price <i class="bi {{ $arrow('unit_price') }} ms-1 small"></i>
                    </th>
                    <th class="text-end sortable" style="min-width: 160px;" wire:click="sort('line_total')">
                        Line Total <i class="bi {{ $arrow('line_total') }} ms-1 small"></i>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $l)
                    @php
                        $isPR = $l->source === 'purchase_request';
                        $label = $isPR ? 'Purchase Request' : 'Monthly Budget';
                        $pill = $isPR ? 'primary' : 'success';

                        // Build document URL using named routes if available; fall back to conventional URLs
                        $url = null;
                        if ($isPR) {
                            $url = \Illuminate\Support\Facades\Route::has('purchaseRequest.detail')
                                ? route('purchaseRequest.detail', $l->doc_id)
                                : url('/purchaserequest/detail/' . $l->doc_id);
                        } else {
                            $url = \Illuminate\Support\Facades\Route::has('monthly.budget.summary.report.show')
                                ? route('monthly.budget.summary.report.show', $l->doc_id)
                                : url('monthlyBudgetSummaryReport/' . $l->doc_id);
                        }
                    @endphp
                    <tr>
                        <td class="text-nowrap">
                            {{ \Illuminate\Support\Carbon::parse($l->expense_date)->format('Y-m-d') }}</td>
                        <td class="text-nowrap">
                            <span
                                class="badge text-bg-{{ $pill }} border border-{{ $pill }}-subtle">{{ $label }}</span>
                        </td>
                        <td>
                            <a href="{{ $url }}" target="_blank" rel="noopener"
                                class="cell-clip text-decoration-none" title="{{ $l->item_name }}">
                                {{ $l->item_name }}
                            </a>
                        </td>
                        <td class="text-end num">{{ number_format($l->quantity, 2, ',', '.') }}</td>
                        <td class="text-nowrap">{{ $l->uom }}</td>
                        <td class="text-end num">Rp {{ number_format($l->unit_price, 2, ',', '.') }}</td>
                        <td class="text-end num fw-semibold">Rp {{ number_format($l->line_total, 2, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox me-1"></i> No lines found.
                        </td>
                    </tr>
                @endforelse
            </tbody>

            @if ($rows->total())
                <tfoot>
                    <tr class="table-light">
                        <th colspan="3" class="text-end">Totals (filtered)</th>
                        <th class="text-end num">{{ number_format($sumQty, 2, ',', '.') }}</th>
                        <th></th>
                        <th></th>
                        <th class="text-end num fw-bold">Rp {{ number_format($sumTotal, 2, ',', '.') }}</th>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    {{-- Pagination --}}
    {{-- Pagination + meta --}}
    @php
        $from = $rows->firstItem() ?? 0;
        $to = $rows->lastItem() ?? 0;
        $total = $rows->total();
    @endphp

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="small text-muted">
            Showing {{ $from }} to {{ $to }} of {{ $total }} results
        </div>
        <div>
            {{ $rows->links('vendor.livewire.bootstrap-noscroll') }}
        </div>
    </div>
</div>

@pushOnce('extraCss')
    <style>
        .detail-table-wrapper {
            max-height: 56vh;
            overflow: auto;
            border-radius: .5rem;
        }

        .table-sticky thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #fff;
            box-shadow: 0 1px 0 rgba(0, 0, 0, .05);
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
