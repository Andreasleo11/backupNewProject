<div class="insp-dash">

    {{-- ════════════════════════════════════════════════════════════════
         STYLES  (scoped inside .insp-dash so nothing leaks)
    ═══════════════════════════════════════════════════════════════════ --}}
    <style>
        :root {
            --dash-bg:        #0d1117;
            --dash-surface:   #161b22;
            --dash-card:      #1c2230;
            --dash-border:    rgba(255,255,255,.08);
            --dash-accent1:   #4f8ef7;
            --dash-accent2:   #34d399;
            --dash-accent3:   #fb923c;
            --dash-accent4:   #f472b6;
            --dash-text:      #e6edf3;
            --dash-muted:     #8b949e;
            --dash-radius:    14px;
            --dash-shadow:    0 4px 24px rgba(0,0,0,.45);
        }

        .insp-dash { background: var(--dash-bg); min-height: 100vh; padding: 1.75rem 1.5rem 3rem; color: var(--dash-text); font-family: 'Inter', 'Segoe UI', sans-serif; }

        /* ── header ── */
        .dash-header { display: flex; flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: center; margin-bottom: 1.75rem; }
        .dash-header h1 { font-size: 1.5rem; font-weight: 700; margin: 0; background: linear-gradient(135deg, var(--dash-accent1), var(--dash-accent2)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .dash-header .dash-subtitle { color: var(--dash-muted); font-size: .875rem; margin: 0; }

        /* ── filter bar ── */
        .filter-bar { background: var(--dash-surface); border: 1px solid var(--dash-border); border-radius: var(--dash-radius); padding: 1rem 1.25rem; margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: .75rem; align-items: flex-end; }
        .filter-bar label { font-size: .75rem; color: var(--dash-muted); margin-bottom: .25rem; display: block; }
        .filter-bar .form-control,
        .filter-bar .form-select { background: var(--dash-card); border: 1px solid var(--dash-border); color: var(--dash-text); border-radius: 8px; font-size: .875rem; }
        .filter-bar .form-control:focus,
        .filter-bar .form-select:focus { background: var(--dash-card); border-color: var(--dash-accent1); box-shadow: 0 0 0 3px rgba(79,142,247,.2); color: var(--dash-text); }

        /* ── KPI cards ── */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
        .kpi-card { background: var(--dash-card); border: 1px solid var(--dash-border); border-radius: var(--dash-radius); padding: 1.25rem 1.25rem 1rem; position: relative; overflow: hidden; transition: transform .2s, box-shadow .2s; }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: var(--dash-shadow); }
        .kpi-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; border-radius: var(--dash-radius) var(--dash-radius) 0 0; }
        .kpi-card.c1::before { background: linear-gradient(90deg, var(--dash-accent1), #818cf8); }
        .kpi-card.c2::before { background: linear-gradient(90deg, var(--dash-accent2), #22d3ee); }
        .kpi-card.c3::before { background: linear-gradient(90deg, var(--dash-accent3), #facc15); }
        .kpi-card.c4::before { background: linear-gradient(90deg, var(--dash-accent4), #e879f9); }
        .kpi-card.c5::before { background: linear-gradient(90deg, #818cf8, var(--dash-accent1)); }
        .kpi-card.c6::before { background: linear-gradient(90deg, #f87171, var(--dash-accent3)); }
        .kpi-card.c7::before { background: linear-gradient(90deg, #a78bfa, var(--dash-accent4)); }
        .kpi-label { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .08em; color: var(--dash-muted); margin-bottom: .5rem; }
        .kpi-value { font-size: 2rem; font-weight: 700; line-height: 1.1; }
        .kpi-unit  { font-size: .8rem; color: var(--dash-muted); margin-top: .2rem; }

        /* ── chart grid ── */
        .chart-grid { display: grid; grid-template-columns: repeat(12, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
        .chart-col-8  { grid-column: span 8;  }
        .chart-col-4  { grid-column: span 4;  }
        .chart-col-6  { grid-column: span 6;  }
        @media (max-width: 900px) {
            .chart-col-8, .chart-col-4, .chart-col-6 { grid-column: span 12; }
        }

        /* ── panel (card wrapper for charts + tables) ── */
        .panel { background: var(--dash-card); border: 1px solid var(--dash-border); border-radius: var(--dash-radius); padding: 1.25rem; height: 100%; }
        .panel-title { font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: var(--dash-muted); margin-bottom: 1rem; }
        .chart-wrap { position: relative; }

        /* ── data tables ── */
        .dash-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
        .dash-table th { font-size: .7rem; color: var(--dash-muted); font-weight: 600; text-transform: uppercase; letter-spacing: .06em; border-bottom: 1px solid var(--dash-border); padding: .5rem .6rem; white-space: nowrap; }
        .dash-table td { padding: .55rem .6rem; border-bottom: 1px solid rgba(255,255,255,.04); color: var(--dash-text); }
        .dash-table tbody tr:hover td { background: rgba(255,255,255,.03); }
        .dash-table tbody tr:last-child td { border-bottom: none; }

        /* ── badges ── */
        .badge-shift { display: inline-block; padding: .2rem .5rem; border-radius: 6px; font-size: .72rem; font-weight: 700; color: #fff; }
        .badge-shift.s1 { background: var(--dash-accent1); }
        .badge-shift.s2 { background: var(--dash-accent3); }
        .badge-shift.s3 { background: var(--dash-accent2); }

        .rate-bar { height: 6px; border-radius: 3px; background: rgba(255,255,255,.1); overflow: hidden; margin-top: 4px; }
        .rate-bar-fill { height: 100%; border-radius: 3px; }

        .btn-sm-dash { font-size: .7rem; padding: .2rem .6rem; border-radius: 6px; border: 1px solid var(--dash-accent1); color: var(--dash-accent1); background: transparent; text-decoration: none; transition: background .15s; }
        .btn-sm-dash:hover { background: rgba(79,142,247,.15); color: var(--dash-accent1); }

        .empty-state { text-align: center; padding: 2rem; color: var(--dash-muted); font-size: .85rem; }

        /* quick-actions row */
        .actions-row { display: flex; gap: .75rem; flex-wrap: wrap; margin-bottom: 1.5rem; }
        .action-btn { display: inline-flex; align-items: center; gap: .4rem; padding: .55rem 1.1rem; border-radius: 10px; font-size: .82rem; font-weight: 600; text-decoration: none; border: 1px solid var(--dash-border); transition: all .2s; }
        .action-btn.primary { background: var(--dash-accent1); color: #fff; border-color: var(--dash-accent1); }
        .action-btn.primary:hover { background: #3a7ce6; color: #fff; }
        .action-btn.ghost { background: transparent; color: var(--dash-text); }
        .action-btn.ghost:hover { background: rgba(255,255,255,.07); color: var(--dash-text); }
    </style>

    {{-- ════════════════════════════════════════════════════════════════
         HEADER
    ═══════════════════════════════════════════════════════════════════ --}}
    <div class="dash-header">
        <div>
            <h1>Inspection Reports Dashboard</h1>
            <p class="dash-subtitle">Quality overview &mdash; {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d M Y') : '—' }} to {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d M Y') : '—' }}</p>
        </div>
        <div class="actions-row" style="margin-bottom:0">
            <a href="{{ route('inspection-reports.create') }}" class="action-btn primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Report
            </a>
            <a href="{{ route('inspection-reports.index') }}" class="action-btn ghost">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                View All Reports
            </a>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         FILTER BAR
    ═══════════════════════════════════════════════════════════════════ --}}
    <div class="filter-bar">
        <div>
            <label>Date From</label>
            <input type="date" class="form-control form-control-sm" wire:model.live="dateFrom" style="min-width:140px">
        </div>
        <div>
            <label>Date To</label>
            <input type="date" class="form-control form-control-sm" wire:model.live="dateTo" style="min-width:140px">
        </div>
        <div>
            <label>Customer</label>
            <select class="form-select form-select-sm" wire:model.live="customer" style="min-width:160px">
                <option value="">All Customers</option>
                @foreach ($customerOptions as $opt)
                    <option value="{{ $opt }}">{{ $opt }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Part Number</label>
            <input type="text" class="form-control form-control-sm" placeholder="Search part…" wire:model.live.debounce.400ms="partNumber" style="min-width:140px">
        </div>
        <div style="padding-top:1.2rem">
            <button class="action-btn ghost" wire:click="clearFilters" style="padding:.4rem .9rem;font-size:.78rem">
                Reset
            </button>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         KPI CARDS
    ═══════════════════════════════════════════════════════════════════ --}}
    <div class="kpi-grid">
        <div class="kpi-card c1">
            <div class="kpi-label">Total Reports</div>
            <div class="kpi-value" style="color:var(--dash-accent1)">{{ number_format($kpi['total_reports'] ?? 0) }}</div>
            <div class="kpi-unit">in selected period</div>
        </div>
        <div class="kpi-card c2">
            <div class="kpi-label">Total Output</div>
            <div class="kpi-value" style="color:var(--dash-accent2)">{{ number_format($kpi['total_output'] ?? 0) }}</div>
            <div class="kpi-unit">pcs produced</div>
        </div>
        <div class="kpi-card c3">
            <div class="kpi-label">Total Pass</div>
            <div class="kpi-value" style="color:var(--dash-accent2)">{{ number_format($kpi['total_pass'] ?? 0) }}</div>
            <div class="kpi-unit">pcs</div>
        </div>
        <div class="kpi-card c6">
            <div class="kpi-label">Total Reject</div>
            <div class="kpi-value" style="color:#f87171">{{ number_format($kpi['total_reject'] ?? 0) }}</div>
            <div class="kpi-unit">pcs</div>
        </div>
        <div class="kpi-card c4">
            <div class="kpi-label">Avg Pass Rate</div>
            <div class="kpi-value" style="color:var(--dash-accent2)">{{ $kpi['avg_pass_rate'] ?? 0 }}<span style="font-size:1rem">%</span></div>
            <div class="kpi-unit">across all reports</div>
        </div>
        <div class="kpi-card c3">
            <div class="kpi-label">Avg Reject Rate</div>
            <div class="kpi-value" style="color:var(--dash-accent3)">{{ $kpi['avg_reject_rate'] ?? 0 }}<span style="font-size:1rem">%</span></div>
            <div class="kpi-unit">across all reports</div>
        </div>
        <div class="kpi-card c5">
            <div class="kpi-label">Avg NG Sample Rate</div>
            <div class="kpi-value" style="color:#818cf8">{{ $kpi['avg_ng_sample_rate'] ?? 0 }}<span style="font-size:1rem">%</span></div>
            <div class="kpi-unit">NG in sampling</div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         ROW 1 — Trend line (8) + Shift doughnut (4)
    ═══════════════════════════════════════════════════════════════════ --}}
    <div class="chart-grid">
        <div class="chart-col-8">
            <div class="panel">
                <div class="panel-title">Inspections Over Time</div>
                <div class="chart-wrap" style="height:220px">
                    <canvas id="chart-trend"></canvas>
                </div>
            </div>
        </div>
        <div class="chart-col-4">
            <div class="panel">
                <div class="panel-title">Reports by Shift</div>
                <div class="chart-wrap" style="height:220px;display:flex;align-items:center;justify-content:center">
                    <canvas id="chart-shift"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         ROW 2 — Pass/Reject stacked bar (6) + Top Customers bar (6)
    ═══════════════════════════════════════════════════════════════════ --}}
    <div class="chart-grid">
        <div class="chart-col-6">
            <div class="panel">
                <div class="panel-title">Pass vs Reject (Daily)</div>
                <div class="chart-wrap" style="height:220px">
                    <canvas id="chart-pass-reject"></canvas>
                </div>
            </div>
        </div>
        <div class="chart-col-6">
            <div class="panel">
                <div class="panel-title">Top 10 Customers by Reports</div>
                <div class="chart-wrap" style="height:220px">
                    <canvas id="chart-customers"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════════════
         ROW 3 — Tables
    ═══════════════════════════════════════════════════════════════════ --}}
    <div class="chart-grid" style="margin-bottom:0">

        {{-- Top Failing Parts --}}
        <div class="chart-col-6">
            <div class="panel">
                <div class="panel-title">Top Failing Parts (by Avg Reject Rate)</div>
                @if (count($topFailingParts))
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Part No.</th>
                                <th>Part Name</th>
                                <th>Reports</th>
                                <th>Avg Reject %</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topFailingParts as $i => $part)
                                <tr>
                                    <td style="color:var(--dash-muted)">{{ $i + 1 }}</td>
                                    <td class="font-monospace" style="font-size:.78rem">{{ $part['part_number'] }}</td>
                                    <td style="color:var(--dash-muted);font-size:.78rem">{{ Str::limit($part['part_name'], 24) }}</td>
                                    <td>{{ $part['reports'] }}</td>
                                    <td>
                                        <span style="color:{{ $part['avg_reject'] > 5 ? '#f87171' : ($part['avg_reject'] > 2 ? 'var(--dash-accent3)' : 'var(--dash-accent2)') }}; font-weight:700">
                                            {{ $part['avg_reject'] }}%
                                        </span>
                                        <div class="rate-bar">
                                            <div class="rate-bar-fill" style="width:{{ min($part['avg_reject'] * 5, 100) }}%;background:{{ $part['avg_reject'] > 5 ? '#f87171' : 'var(--dash-accent3)' }}"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">No data in selected period</div>
                @endif
            </div>
        </div>

        {{-- Top Problem Types --}}
        <div class="chart-col-6">
            <div class="panel">
                <div class="panel-title">Top Problem Types</div>
                @if (count($topProblemTypes))
                    @php $maxCount = max(array_column($topProblemTypes, 'count')); @endphp
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Count</th>
                                <th style="width:35%">Frequency</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topProblemTypes as $i => $prob)
                                <tr>
                                    <td style="color:var(--dash-muted)">{{ $i + 1 }}</td>
                                    <td>{{ $prob['type'] }}</td>
                                    <td style="font-weight:700">{{ $prob['count'] }}</td>
                                    <td>
                                        <div class="rate-bar">
                                            <div class="rate-bar-fill" style="width:{{ $maxCount ? round($prob['count'] / $maxCount * 100) : 0 }}%;background:var(--dash-accent4)"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">No problems logged in selected period</div>
                @endif
            </div>
        </div>

        {{-- Latest Reports --}}
        <div class="chart-col-6">
            <div class="panel">
                <div class="panel-title">Latest Reports</div>
                @if (count($latestReports))
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Document</th>
                                <th>Customer</th>
                                <th>Shift</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($latestReports as $r)
                                <tr>
                                    <td style="white-space:nowrap;color:var(--dash-muted);font-size:.78rem">{{ $r['inspection_date'] }}</td>
                                    <td class="font-monospace" style="font-size:.76rem">{{ $r['document_number'] }}</td>
                                    <td style="font-size:.8rem">{{ Str::limit($r['customer'], 18) }}</td>
                                    <td>
                                        <span class="badge-shift s{{ $r['shift'] }}">S{{ $r['shift'] }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('inspection-reports.show', $r['id']) }}" class="btn-sm-dash">View</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">No reports in selected period</div>
                @endif
            </div>
        </div>

        {{-- Dimension NG Failures by Area --}}
        <div class="chart-col-6">
            <div class="panel">
                <div class="panel-title">Dimension NG Failures by Area</div>
                @if (count($dimensionFailures))
                    @php $maxNg = max(array_column($dimensionFailures, 'ng_count')); @endphp
                    <table class="dash-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Area</th>
                                <th>NG Count</th>
                                <th style="width:35%">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dimensionFailures as $i => $dim)
                                <tr>
                                    <td style="color:var(--dash-muted)">{{ $i + 1 }}</td>
                                    <td>{{ $dim['area'] }}</td>
                                    <td style="font-weight:700;color:#f87171">{{ $dim['ng_count'] }}</td>
                                    <td>
                                        <div class="rate-bar">
                                            <div class="rate-bar-fill" style="width:{{ $maxNg ? round($dim['ng_count'] / $maxNg * 100) : 0 }}%;background:#f87171"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="empty-state">No dimension NG in selected period</div>
                @endif
            </div>
        </div>

    </div>{{-- /.chart-grid --}}

    {{-- ════════════════════════════════════════════════════════════════
         CHART.JS + initialisation
    ═══════════════════════════════════════════════════════════════════ --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
    (function () {
        // ── Shared palette ──────────────────────────────────────────────
        var accent1 = '#4f8ef7';
        var accent2 = '#34d399';
        var accent3 = '#fb923c';
        var accent4 = '#f472b6';
        var accent5 = '#818cf8';
        var red     = '#f87171';
        var muted   = '#8b949e';
        var gridColor = 'rgba(255,255,255,0.06)';

        Chart.defaults.color = muted;
        Chart.defaults.borderColor = gridColor;
        Chart.defaults.font.family = '"Inter", "Segoe UI", sans-serif';
        Chart.defaults.font.size   = 11;

        // ── Data injected from Livewire ─────────────────────────────────
        var trendData   = @json($trendChart);
        var shiftData   = @json($shiftChart);
        var custData    = @json($customerChart);
        var prData      = @json($passRejectChart);

        // ── Helpers ─────────────────────────────────────────────────────
        function qs(id){ return document.getElementById(id); }

        function makeGrad(ctx, colorA, colorB) {
            var g = ctx.createLinearGradient(0, 0, 0, ctx.canvas.height);
            g.addColorStop(0,   colorA + 'cc');
            g.addColorStop(1,   colorA + '11');
            return g;
        }

        // ── 1. Inspections over time (Line) ─────────────────────────────
        (function(){
            var el = qs('chart-trend'); if (!el) return;
            var ctx = el.getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: trendData.labels || [],
                    datasets: [{
                        label: 'Inspections',
                        data:  trendData.data || [],
                        borderColor: accent1,
                        backgroundColor: makeGrad(ctx, accent1, 'transparent'),
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointBackgroundColor: accent1,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
                    scales: {
                        x: { grid: { color: gridColor }, ticks: { maxTicksLimit: 10 } },
                        y: { grid: { color: gridColor }, beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        })();

        // ── 2. Reports by shift (Doughnut) ──────────────────────────────
        (function(){
            var el = qs('chart-shift'); if (!el) return;
            new Chart(el.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: shiftData.labels || [],
                    datasets: [{
                        data: shiftData.data || [],
                        backgroundColor: [accent1, accent3, accent2],
                        borderColor: '#1c2230',
                        borderWidth: 3,
                        hoverOffset: 6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 14, boxWidth: 12 } },
                        tooltip: { callbacks: { label: function(c){ return ' ' + c.label + ': ' + c.parsed; } } }
                    }
                }
            });
        })();

        // ── 3. Pass vs Reject stacked bar ───────────────────────────────
        (function(){
            var el = qs('chart-pass-reject'); if (!el) return;
            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: prData.labels || [],
                    datasets: [
                        { label: 'Pass', data: prData.pass || [], backgroundColor: accent2 + 'cc', stack: 'qty', borderRadius: 3 },
                        { label: 'Reject', data: prData.reject || [], backgroundColor: red + 'cc',    stack: 'qty', borderRadius: 3 },
                    ]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { position: 'top', labels: { boxWidth: 10, padding: 10 } }, tooltip: { mode: 'index' } },
                    scales: {
                        x: { stacked: true, grid: { color: gridColor }, ticks: { maxTicksLimit: 10 } },
                        y: { stacked: true, grid: { color: gridColor }, beginAtZero: true, ticks: { precision: 0 } }
                    }
                }
            });
        })();

        // ── 4. Top customers horizontal bar ─────────────────────────────
        (function(){
            var el = qs('chart-customers'); if (!el) return;
            new Chart(el.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: custData.labels || [],
                    datasets: [{
                        label: 'Reports',
                        data: custData.data || [],
                        backgroundColor: accent5 + 'bb',
                        hoverBackgroundColor: accent5,
                        borderRadius: 4,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(c){ return ' ' + c.parsed.x + ' reports'; } } } },
                    scales: {
                        x: { grid: { color: gridColor }, beginAtZero: true, ticks: { precision: 0 } },
                        y: { grid: { color: gridColor } }
                    }
                }
            });
        })();

    })();
    </script>
</div>
