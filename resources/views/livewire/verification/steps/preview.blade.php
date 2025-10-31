<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <div class="fw-semibold">Preview</div>
        <div class="text-muted small">Review all data before saving</div>

        <div class="ms-auto d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-copy-summary" data-bs-toggle="tooltip"
                title="Copy plain-text summary">
                <i class="bi bi-clipboard-check"></i> Copy summary
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
    <div class="px-3 pb-3">
        <div class="row row-cols-1 row-cols-md-4 g-3">
            <div class="col">
                <div class="summary-tile">
                    <div class="summary-label">Receive Date</div>
                    <div class="summary-value">{{ $form['rec_date'] ?? '—' }}</div>
                </div>
            </div>
            <div class="col">
                <div class="summary-tile">
                    <div class="summary-label">Verify Date</div>
                    <div class="summary-value">{{ $form['verify_date'] ?? '—' }}</div>
                </div>
            </div>
            <div class="col">
                <div class="summary-tile">
                    <div class="summary-label">Customer</div>
                    <div class="summary-value">{{ $form['customer'] ?? '—' }}</div>
                </div>
            </div>
            <div class="col">
                <div class="summary-tile">
                    <div class="summary-label">Invoice #</div>
                    <div class="summary-value">{{ $form['invoice_number'] ?? '—' }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ITEMS TABLE --}}
    <div class="px-3 pb-3">
        <div class="preview-toolbar d-flex align-items-center gap-2 mb-2">
            <h6 class="mb-0 text-muted">Preview</h6>
            <div class="vr"></div>
            <button type="button" class="btn btn-sm btn-outline-secondary"
                onclick="window.Preview.toggleAllDefects(true)">
                <i class="bi bi-arrows-expand me-1"></i>Expand all
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary"
                onclick="window.Preview.toggleAllDefects(false)">
                <i class="bi bi-arrows-collapse me-1"></i>Collapse all
            </button>
        </div>

        <div class="table-responsive preview-table-wrap">
            <table class="table table-sm align-middle preview-table">
                <thead>
                    <tr>
                        <th style="min-width:260px">Part</th>
                        <th class="text-end">Rec Qty</th>
                        <th class="text-end">Verify Qty</th>
                        <th class="text-end">Can Use</th>
                        <th class="text-end">Can’t Use</th>
                        <th class="text-end">Price</th>
                        <th class="text-end">Line</th>
                    </tr>
                </thead>

                <tbody>
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
                            $hi = collect($defects)->where('severity', 'HIGH')->count();
                            $md = collect($defects)->where('severity', 'MEDIUM')->count();
                            $lo = collect($defects)->where('severity', 'LOW')->count();

                            $collapseId = "pv-def-{$idx}";
                        @endphp

                        <tr class="pv-row">
                            <td>
                                <div class="pv-part">
                                    <div class="pv-title fw-semibold text-truncate">
                                        {{ $row['part_name'] ?? '—' }}
                                    </div>

                                    {{-- Defect ribbon (always visible if exists) --}}
                                    @if (!empty($defects))
                                        <div class="pv-defect-ribbon">
                                            <span class="chip sev-high {{ $hi ? '' : 'chip-muted' }}"><i
                                                    class="bi bi-bug-fill me-1"></i>HIGH {{ $hi }}</span>
                                            <span class="chip sev-med  {{ $md ? '' : 'chip-muted' }}"><i
                                                    class="bi bi-bug me-1"></i>MED {{ $md }}</span>
                                            <span class="chip sev-low  {{ $lo ? '' : 'chip-muted' }}"><i
                                                    class="bi bi-bug me-1"></i>LOW {{ $lo }}</span>

                                            @php
                                                $srcCounts = collect($defects)->groupBy('source')->map->count();
                                                $srcMeta = function ($src) {
                                                    $map = [
                                                        'SUPPLIER' => ['Supplier', 'bi-box-seam', 'src-supplier'],
                                                        'INTERNAL' => ['Internal', 'bi-wrench', 'src-internal'],
                                                        'LOGISTICS' => ['Logistics', 'bi-truck', 'src-logistics'],
                                                        'CUSTOMER' => ['Customer', 'bi-person', 'src-customer'],
                                                    ];
                                                    return $map[$src] ?? [
                                                        ucfirst(strtolower($src ?: 'Unknown')),
                                                        'bi-info-circle',
                                                        'src-unknown',
                                                    ];
                                                };
                                            @endphp
                                            @foreach ($srcCounts as $sKey => $cnt)
                                                @php [$label,$icon,$cls] = $srcMeta($sKey); @endphp
                                                <span class="chip chip-src {{ $cls }}"><i
                                                        class="bi {{ $icon }} me-1"></i>{{ $label }}
                                                    {{ $cnt }}</span>
                                            @endforeach
                                        </div>

                                        {{-- Details toggle for full table --}}
                                        <details class="pv-defects mt-2" data-preview-defects>
                                            <summary class="defects-summary">
                                                <i class="bi bi-chevron-right me-1"></i>
                                                <strong>Show defects ({{ $hi + $md + $lo }})</strong>
                                            </summary>

                                            <div class="mt-2">
                                                <div class="table-responsive">
                                                    <table class="table table-sm mb-0 defects-table">
                                                        <thead>
                                                            <tr class="text-muted small">
                                                                <th style="width:12%">Code</th>
                                                                <th style="width:28%">Name</th>
                                                                <th style="width:18%">Source</th>
                                                                <th class="text-end" style="width:12%">Qty</th>
                                                                <th>Notes</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($defects as $d)
                                                                @php
                                                                    $sev = $d['severity'] ?? 'LOW';
                                                                    $sevCls = match ($sev) {
                                                                        'HIGH' => 'sev-high',
                                                                        'MEDIUM' => 'sev-med',
                                                                        default => 'sev-low',
                                                                    };
                                                                    [$sLabel, $sIcon, $sCls] = $srcMeta(
                                                                        $d['source'] ?? '',
                                                                    );
                                                                @endphp
                                                                <tr class="defect-row {{ $sevCls }}">
                                                                    <td class="font-monospace">{{ $d['code'] ?? '—' }}
                                                                    </td>
                                                                    <td class="fw-semibold">{{ $d['name'] ?? '—' }}
                                                                    </td>
                                                                    <td>
                                                                        <span
                                                                            class="badge badge-src {{ $sCls }}">
                                                                            <i
                                                                                class="bi {{ $sIcon }} me-1"></i>{{ $sLabel }}
                                                                        </span>
                                                                    </td>
                                                                    <td class="text-end">
                                                                        {{ rtrim(rtrim(number_format((float) ($d['quantity'] ?? 0), 4, '.', ''), '0'), '.') }}
                                                                    </td>
                                                                    <td class="text-muted">{{ $d['notes'] ?? '—' }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </td>

                            <td class="text-end">{{ rtrim(rtrim(number_format($rq, 4, '.', ''), '0'), '.') }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format($vq, 4, '.', ''), '0'), '.') }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format($can, 4, '.', ''), '0'), '.') }}</td>
                            <td class="text-end">{{ rtrim(rtrim(number_format($cant, 4, '.', ''), '0'), '.') }}</td>
                            <td class="text-end">{{ $row['currency'] ?? '' }} {{ number_format($price, 2) }}</td>
                            <td class="text-end fw-semibold">{{ $row['currency'] ?? '' }}
                                {{ number_format($line, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No items.</td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot class="preview-foot">
                    @foreach ($grandTotals as $gr)
                        <tr>
                            <th colspan="6" class="text-end">Total ({{ $gr['currency'] }})</th>
                            <th class="text-end fs-6">
                                <span class="total-chip">{{ $gr['currency'] }}
                                    {{ number_format($gr['sum'], 2) }}</span>
                            </th>
                        </tr>
                    @endforeach
                </tfoot>
            </table>
        </div>
    </div>
</div>

@pushOnce('extraCss')
    <style>
        /* Print-friendly: remove card shadow/borders and show all details open */
        @media print {
            .card {
                box-shadow: none !important;
                border: 0 !important;
            }

            .card-header,
            .btn,
            [data-bs-toggle="tooltip"] {
                display: none !important;
            }

            details.defects-details[open]>summary {
                list-style: none;
            }

            details.defects-details {
                page-break-inside: avoid;
            }
        }

        .summary-tile {
            background: var(--bs-body-tertiary);
            border-radius: .75rem;
            padding: .9rem .95rem;
            height: 100%;
        }

        .summary-label {
            font-size: .8rem;
            color: var(--bs-secondary-color, #6c757d);
            margin-bottom: .25rem;
        }

        .summary-value {
            font-weight: 600;
            word-break: break-word;
        }

        .preview-table-wrap {
            overflow: hidden;
        }

        .preview-table thead th {
            background: var(--bs-light, #f8f9fa);
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .preview-table tbody tr:nth-child(even) {
            background: color-mix(in srgb, var(--bs-body-bg) 96%, black);
        }

        .preview-table td,
        .preview-table th {
            vertical-align: middle;
        }

        .preview-table tfoot th,
        .preview-table tfoot td {
            background: color-mix(in srgb, var(--bs-body-bg) 92%, black);
        }

        details.defects-details summary {
            cursor: pointer;
            user-select: none;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
        }

        details.defects-details[open] summary {
            color: var(--bs-primary);
        }

        /* ---------- Preview Table Look ---------- */
        .preview-table-wrap {
            overflow: clip;
            background: var(--bs-body-bg);
            box-shadow: 0 1px 0 color-mix(in srgb, var(--bs-body-color) 6%, transparent);
        }

        .preview-table thead th {
            position: sticky;
            top: 0;
            background: color-mix(in srgb, var(--bs-body-bg) 96%, white);
            z-index: 1;
            font-weight: 700;
            border-bottom: 1px solid color-mix(in srgb, var(--bs-body-color) 8%, transparent);
        }

        .preview-table tbody tr:hover {
            background: color-mix(in srgb, var(--bs-body-color) 4%, transparent);
        }

        .preview-foot th {
            position: sticky;
            bottom: 0;
            background: color-mix(in srgb, var(--bs-body-bg) 96%, white);
            border-top: 1px solid color-mix(in srgb, var(--bs-body-color) 12%, transparent);
        }

        /* ---------- Part Cell ---------- */
        .pv-part {
            display: grid;
            gap: .35rem;
        }

        .pv-meta {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
        }

        /* ---------- Defect Ribbon ---------- */
        .pv-defect-ribbon {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-size: .75rem;
            padding: .18rem .5rem;
            border-radius: 999px;
            border: 1px solid transparent;
            font-weight: 600;
        }

        .chip-muted {
            opacity: .5;
            border-color: color-mix(in srgb, var(--bs-body-color) 10%, transparent);
        }

        .sev-high {
            --sev-bg: color-mix(in srgb, #dc3545 12%, transparent);
            --sev-br: #dc3545;
            --sev-fg: #dc3545;
        }

        .sev-med {
            --sev-bg: color-mix(in srgb, #ffc107 16%, transparent);
            --sev-br: #ffc107;
            --sev-fg: #b08900;
        }

        .sev-low {
            --sev-bg: color-mix(in srgb, #198754 14%, transparent);
            --sev-br: #198754;
            --sev-fg: #198754;
        }

        .chip.sev-high {
            background: var(--sev-bg);
            color: var(--sev-fg);
            border-color: var(--sev-br);
        }

        .chip.sev-med {
            background: var(--sev-bg);
            color: var(--sev-fg);
            border-color: var(--sev-br);
        }

        .chip.sev-low {
            background: var(--sev-bg);
            color: var(--sev-fg);
            border-color: var(--sev-br);
        }

        /* Sources */
        .chip-src,
        .badge-src {
            border: 1px solid transparent;
        }

        .src-supplier {
            background: color-mix(in srgb, #0d6efd 12%, transparent);
            border-color: #0d6efd;
            color: #0d6efd;
        }

        .src-internal {
            background: color-mix(in srgb, #20c997 14%, transparent);
            border-color: #20c997;
            color: #198754;
        }

        .src-logistics {
            background: color-mix(in srgb, #6f42c1 14%, transparent);
            border-color: #6f42c1;
            color: #6f42c1;
        }

        .src-customer {
            background: color-mix(in srgb, #fd7e14 14%, transparent);
            border-color: #fd7e14;
            color: #fd7e14;
        }

        .src-unknown {
            background: color-mix(in srgb, #6c757d 14%, transparent);
            border-color: #6c757d;
            color: #6c757d;
        }

        /* ---------- Defects details toggle ---------- */
        .defects-summary {
            cursor: pointer;
            user-select: none;
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            color: var(--bs-secondary-color, #6c757d);
        }

        .pv-defects[open]>.defects-summary {
            color: var(--bs-primary);
        }

        .defects-summary .bi-chevron-right {
            transition: transform .2s ease;
        }

        .pv-defects[open] .defects-summary .bi-chevron-right {
            transform: rotate(90deg);
        }

        /* ---------- Defects table emphasis ---------- */
        .defects-table .defect-row {
            --row-accent: var(--sev-br, #adb5bd);
            --row-tint: var(--sev-bg, transparent);
            border-left: .35rem solid var(--row-accent);
            background: var(--row-tint);
        }

        .badge-sev {
            background: var(--sev-bg);
            color: var(--sev-fg);
            border: 1px solid var(--sev-br);
            font-weight: 700;
        }

        /* ---------- Totals ---------- */
        .total-chip {
            display: inline-block;
            padding: .2rem .5rem;
            border-radius: .5rem;
            background: color-mix(in srgb, #0ea5e9 12%, transparent);
            border: 1px solid color-mix(in srgb, #0ea5e9 50%, transparent);
            font-weight: 700;
            color: #0ea5e9;
        }

        /* ---------- Toolbar ---------- */
        .preview-toolbar .vr {
            width: 1px;
            height: 18px;
            background: color-mix(in srgb, var(--bs-body-color) 20%, transparent);
        }

        /* Dark-mode tweaks */
        @media (prefers-color-scheme: dark) {

            .preview-table thead th,
            .preview-foot th {
                background: color-mix(in srgb, var(--bs-body-bg) 85%, white);
            }
        }
    </style>
@endPushOnce



@pushOnce('extraJs')
    <script>
        // Copy a compact textual summary for chats/emails
        document.getElementById('btn-copy-summary')?.addEventListener('click', () => {
            const root = document.currentScript.closest('.card') || document.body;
            const rows = [...root.querySelectorAll('.preview-table tbody tr')];
            const lines = [];

            rows.forEach(tr => {
                const cells = tr.querySelectorAll('td');
                if (!cells.length) return;
                const part = cells[0]?.innerText.trim().replace(/\s+/g, ' ');
                const rec = cells[1]?.innerText.trim();
                const ver = cells[2]?.innerText.trim();
                const can = cells[3]?.innerText.trim();
                const cant = cells[4]?.innerText.trim();
                const price = cells[5]?.innerText.trim();
                const line = cells[6]?.innerText.trim();
                lines.push(
                    `${part} | Rec:${rec} | Verify:${ver} | OK:${can} | NG:${cant} | ${price} | ${line}`
                );
            });

            if (!lines.length) {
                navigator.clipboard.writeText('No items.');
            } else {
                navigator.clipboard.writeText(lines.join('\n'));
            }

            // quick feedback
            const btn = document.getElementById('btn-copy-summary');
            const original = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-clipboard-check-fill"></i> Copied';
            setTimeout(() => btn.innerHTML = original, 1200);
        });

        window.Preview = {
            toggleAllDefects(open) {
                document.querySelectorAll('[data-preview-defects]').forEach(d => {
                    if (open) d.setAttribute('open', '');
                    else d.removeAttribute('open');
                });
            }
        };
    </script>
@endPushOnce
