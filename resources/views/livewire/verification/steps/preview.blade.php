<div x-data="{ globalOpen: true }" class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50/50 flex flex-wrap gap-3 justify-between items-center">
        <div>
            <h5 class="text-sm font-bold text-slate-500 uppercase tracking-wider">
                <i class="bi bi-eye mr-1.5"></i> Preview Report
            </h5>
            <div class="text-xs text-slate-400 mt-0.5">Review all details before submitting for approval</div>
        </div>

        <div class="flex gap-2">
            <button type="button" class="inline-flex items-center justify-center font-semibold rounded-lg border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 text-xs px-3.5 py-1.5 shadow-sm transition-colors" onclick="window.print()">
                <i class="bi bi-printer mr-1.5"></i> Print
            </button>
        </div>
    </div>

    @php
        $byCurr = collect($items)->groupBy(fn($r) => trim($r['currency'] ?? 'IDR') ?: 'IDR');
        $grandTotals = $byCurr
            ->map(function ($rows, $cur) {
                return [
                    'currency' => $cur,
                    'sum' => $rows->sum(fn($r) => (float) ($r['verify_quantity'] ?? 0) * (float) ($r['price'] ?? 0)),
                ];
            })
            ->values();
    @endphp

    {{-- HEADER SUMMARY --}}
    <div class="px-6 py-5 bg-slate-50/30 border-t border-b border-slate-200">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            {{-- Receive Date --}}
            <div class="p-4 rounded-xl border border-slate-200 bg-white shadow-sm flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="bi bi-calendar2-check"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-1.5">Receive Date</div>
                    <div class="text-xs font-bold text-slate-800 truncate">{{ $form['rec_date'] ?? '—' }}</div>
                </div>
            </div>

            {{-- Verify Date --}}
            <div class="p-4 rounded-xl border border-slate-200 bg-white shadow-sm flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="bi bi-calendar2-event"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-1.5">Verify Date</div>
                    <div class="text-xs font-bold text-slate-800 truncate">{{ $form['verify_date'] ?? '—' }}</div>
                </div>
            </div>

            {{-- Customer --}}
            <div class="p-4 rounded-xl border border-slate-200 bg-white shadow-sm flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="bi bi-building"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-1.5">Customer</div>
                    <div class="text-xs font-bold text-slate-800 truncate" title="{{ $form['customer'] ?? '—' }}">
                        {{ $form['customer'] ?? '—' }}
                    </div>
                </div>
            </div>

            {{-- Invoice Number --}}
            <div class="p-4 rounded-xl border border-slate-200 bg-white shadow-sm flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-sm flex-shrink-0">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
                <div class="min-w-0">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider leading-none mb-1.5">Invoice Number</div>
                    <div class="text-xs font-bold text-slate-800 truncate" title="{{ $form['invoice_number'] ?? '—' }}">
                        {{ $form['invoice_number'] ?? '—' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ITEMS TABLE --}}
    <div class="p-6">
        <div class="flex items-center justify-between gap-3 mb-4">
            <h6 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Items Summary</h6>
            <div class="flex gap-2">
                <button type="button" class="inline-flex items-center justify-center font-semibold rounded border border-slate-200 text-slate-650 hover:bg-slate-50 text-[10px] px-2 py-1 transition-colors shadow-sm"
                    @click="globalOpen = true; $dispatch('toggle-defects', { open: true })">
                    <i class="bi bi-arrows-expand mr-1"></i>Expand all
                </button>
                <button type="button" class="inline-flex items-center justify-center font-semibold rounded border border-slate-200 text-slate-650 hover:bg-slate-50 text-[10px] px-2 py-1 transition-colors shadow-sm"
                    @click="globalOpen = false; $dispatch('toggle-defects', { open: false })">
                    <i class="bi bi-arrows-collapse mr-1"></i>Collapse all
                </button>
            </div>
        </div>

        <div class="overflow-x-auto border rounded-xl shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm text-slate-700">
                <thead class="bg-slate-50">
                    <tr class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-4" style="min-width: 280px;">Part</th>
                        <th class="py-3 px-4 text-right" style="width: 10%">Rec Qty</th>
                        <th class="py-3 px-4 text-right" style="width: 10%">Verify Qty</th>
                        <th class="py-3 px-4 text-right" style="width: 10%">Can Use</th>
                        <th class="py-3 px-4 text-right" style="width: 10%">Can’t Use</th>
                        <th class="py-3 px-4 text-right" style="width: 12%">Price</th>
                        <th class="py-3 px-4 text-right" style="width: 13%">Line Total</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200 bg-white">
                    @php $total = 0; @endphp
                    @forelse ($items as $idx => $row)
                        @php
                            $rq = (float) ($row['rec_quantity'] ?? 0);
                            $vq = (float) ($row['verify_quantity'] ?? 0);
                            $can = (float) ($row['can_use'] ?? 0);
                            $cant = (float) ($row['cant_use'] ?? 0);
                            $price = (float) ($row['price'] ?? 0);
                            $line = $vq * $price;
                            $total += $line;

                            $defects = $row['defects'] ?? [];
                        @endphp

                        <tr class="pv-row hover:bg-slate-50/30 transition-colors">
                            <td class="py-4 px-4">
                                <div class="flex flex-col gap-1">
                                    <div class="font-semibold text-slate-800 text-sm">
                                        {{ $row['part_name'] ?? '—' }}
                                    </div>

                                    {{-- Collapsible Defect Chips List --}}
                                    @if (!empty($defects))
                                        <div x-data="{ showDefects: true }"
                                             @toggle-defects.window="showDefects = $event.detail.open"
                                             class="mt-2 text-slate-550">
                                            
                                            <div class="flex items-center justify-between border-b border-slate-100 pb-1 mb-2">
                                                <button type="button" class="text-slate-500 hover:text-slate-850 cursor-pointer font-bold text-[10px] uppercase tracking-wider inline-flex items-center gap-1.5 select-none focus:outline-none"
                                                    @click="showDefects = !showDefects">
                                                    <i class="bi text-[10px] transition-transform duration-200" :class="showDefects ? 'bi-chevron-down' : 'bi-chevron-right'"></i>
                                                    <span>Defect Details ({{ count($defects) }})</span>
                                                </button>
                                                
                                                {{-- Inline Source Summary (shown only when collapsed) --}}
                                                <div x-show="!showDefects" class="flex gap-1.5 text-[9px] font-semibold text-slate-400">
                                                    @php
                                                        $srcCounts = collect($defects)->groupBy('source')->map->count();
                                                    @endphp
                                                    @foreach ($srcCounts as $sKey => $cnt)
                                                        <span>{{ ucfirst(strtolower($sKey)) }}: {{ $cnt }}</span>
                                                        @if(!$loop->last) <span class="text-slate-200">|</span> @endif
                                                    @endforeach
                                                </div>
                                            </div>

                                            {{-- Defects Premium Card Chips --}}
                                            <div x-show="showDefects" x-collapse class="flex flex-col gap-1.5 mt-2">
                                                @foreach ($defects as $d)
                                                    <div class="flex items-start justify-between gap-3 p-2 rounded-lg border border-slate-100 bg-slate-50/50 hover:bg-slate-50 transition-colors">
                                                        <div class="flex flex-col gap-0.5">
                                                            <div class="flex items-center gap-2">
                                                                {{-- Source Badge --}}
                                                                @php
                                                                    $srcColors = match($d['source'] ?? 'DAIJO') {
                                                                        'CUSTOMER' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                                        'SUPPLIER' => 'bg-amber-50 text-amber-700 border-amber-200',
                                                                        default => 'bg-blue-50 text-blue-700 border-blue-200',
                                                                    };
                                                                    $srcIcons = match($d['source'] ?? 'DAIJO') {
                                                                        'CUSTOMER' => 'bi-person-badge',
                                                                        'SUPPLIER' => 'bi-box-seam',
                                                                        default => 'bi-building',
                                                                    };
                                                                @endphp
                                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[8px] font-bold border {{ $srcColors }}">
                                                                    <i class="bi {{ $srcIcons }} text-[8px]"></i>
                                                                    {{ $d['source'] ?? 'DAIJO' }}
                                                                </span>
                                                                <span class="font-semibold text-slate-800 text-[11px]">{{ $d['name'] ?? '—' }}</span>
                                                            </div>
                                                            @if(!empty($d['notes']))
                                                                <div class="text-[10px] text-slate-400 italic pl-1 mt-0.5">
                                                                    <i class="bi bi-chat-left-text text-[9px] mr-1"></i>"{{ $d['notes'] }}"
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="text-right flex items-center gap-1 font-mono text-[10px] font-bold text-slate-900 bg-white border border-slate-200 px-1.5 py-0.5 rounded shadow-sm self-center">
                                                            <span class="text-slate-400 text-[9px] font-medium">Qty:</span>
                                                            <span>{{ number_format((int) ($d['quantity'] ?? 0)) }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <td class="text-right pr-4 py-3 font-medium">{{ number_format((int) $rq) }}</td>
                            <td class="text-right pr-4 py-3 font-medium">{{ number_format((int) $vq) }}</td>
                            <td class="text-right pr-4 py-3 font-medium">{{ number_format((int) $can) }}</td>
                            <td class="text-right pr-4 py-3 text-red-600 font-bold">{{ number_format((int) $cant) }}</td>
                            <td class="text-right pr-4 py-3 text-slate-400 font-mono">{{ $row['currency'] ?? '' }} {{ number_format($price, 2) }}</td>
                            <td class="text-right pr-4 py-3 font-extrabold text-slate-900 font-mono">{{ $row['currency'] ?? '' }} {{ number_format($line, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-400 py-12">No items added to the report.</td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                    @foreach ($grandTotals as $gr)
                        <tr>
                            <td colspan="6" class="text-right pr-4 py-3.5 text-slate-500 font-semibold">Total ({{ $gr['currency'] }}):</td>
                            <td class="text-right pr-4 py-3.5 font-extrabold text-blue-600 text-sm font-mono">
                                {{ $gr['currency'] }} {{ number_format($gr['sum'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@pushOnce('extraCss')
    <style>
        @media print {
            .card {
                box-shadow: none !important;
                border: 0 !important;
            }
            .card-header, .btn, [data-bs-toggle="tooltip"] {
                display: none !important;
            }
            /* Ensure Alpine collapsed elements are open for print */
            [x-show="showDefects"] {
                display: block !important;
            }
        }
    </style>
@endPushOnce

