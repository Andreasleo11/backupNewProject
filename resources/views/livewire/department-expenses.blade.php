@php
    $monthLabel = \Illuminate\Support\Carbon::parse(($month ?? now()->format('Y-m')) . '-01')->isoFormat('MMMM YYYY');
    $grandTotal = $totals->sum('total_expense');
    $deptSelected = $deptId ? optional($totals->firstWhere('dept_id', $deptId))->dept_name : null;
@endphp

<div class="container-fluid px-0">
    {{-- Top header / toolbar --}}
    <div class="card border-0 shadow-sm mb-3 overflow-hidden design-hero">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-wrap align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="hero-avatar d-none d-sm-flex">
                        <i class="bi bi-graph-up"></i>
                    </div>
                    <div>
                        <h5 class="mb-1 fw-bold">Department Expenses</h5>
                        <div class="text-muted small">Unified from Purchase Requests & Monthly Budget summary</div>
                    </div>
                </div>

                <div class="ms-auto d-flex flex-wrap align-items-center gap-2">
                    <div class="input-group input-group-sm" style="width: 220px;">
                        <span class="input-group-text bg-white"><i class="bi bi-calendar2-month"></i></span>
                        <input type="month" class="form-control" wire:model.live="month">
                    </div>

                    <div class="input-group input-group-sm" style="width: 260px;">
                        <span class="input-group-text bg-white"><i class="bi bi-person-check"></i></span>
                        <select class="form-select" wire:model.live="prSigner">
                            <option value="">All PR approvers</option>
                            @foreach ($prSigners as $signer)
                                <option value="{{ $signer }}">{{ $signer }}</option>
                            @endforeach
                        </select>
                    </div>

                    @if ($deptId)
                        <button class="btn btn-outline-secondary btn-sm" wire:click="clearDetail">
                            <i class="bi bi-arrow-left-circle me-1"></i> Back to all
                        </button>
                    @endif
                </div>
            </div>

            {{-- KPI chips --}}
            <div class="d-flex flex-wrap gap-2 mt-3">
                <span class="badge rounded-pill text-bg-light border fw-normal">
                    <i class="bi bi-calendar2-week me-1"></i> Period:
                    <span class="fw-semibold ms-1">{{ $monthLabel }}</span>
                </span>
                <span class="badge rounded-pill text-bg-light border fw-normal">
                    <i class="bi bi-cash-coin me-1"></i> Total:
                    <span class="fw-semibold ms-1">Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                </span>
                <span class="badge rounded-pill text-bg-light border fw-normal">
                    <i class="bi bi-diagram-3 me-1"></i> Departments:
                    <span class="fw-semibold ms-1">{{ $totals->count() }}</span>
                </span>
                @if ($deptSelected)
                    <span
                        class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle fw-normal">
                        <i class="bi bi-pin-angle me-1"></i> Selected: <span
                            class="fw-semibold ms-1">{{ $deptSelected }}</span>
                    </span>
                @endif
                @if ($prSigner)
                    <span class="badge rounded-pill text-bg-light border fw-normal">
                        <i class="bi bi-person-check me-1"></i> PR Approver:
                        <span class="fw-semibold ms-1">{{ $prSigner }}</span>
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Chart card --}}
    <div class="card border-0 shadow-sm mb-3" wire:key="dept-expenses-chart" x-data="departmentChart()"
        x-init="init({
            labels: @js($totals->pluck('dept_name')->values()),
            data: @js($totals->pluck('total_expense')->map(fn($v) => (float) $v)->values()),
            deptIds: @js($totals->pluck('dept_id')->map(fn($v) => (int) $v)->values())
        })">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0">Expenses by Department</h6>
                <small class="text-muted">
                    {{ \Illuminate\Support\Carbon::parse($month . '-01')->isoFormat('MMMM YYYY') }}
                </small>
            </div>

            <div class="position-relative chart-shell">
                {{-- Livewire must ignore ONLY the canvas, not the overlay --}}
                <div wire:ignore class="h-100">
                    <canvas x-ref="canvas" role="img" aria-label="Department expenses bar chart"></canvas>
                </div>

                {{-- This overlay NOW sits OUTSIDE wire:ignore and only appears while loading --}}
                <div class="chart-overlay" wire:loading wire:target="month,showDetail,clearDetail"></div>
            </div>

            {{--  
                <div class="d-flex justify-content-end mt-2">
                    <button type="button" class="btn btn-link btn-sm text-decoration-none"
                        @click="$dispatch('chart:clearSelection')">
                        <i class="bi bi-x-circle me-1"></i> Clear highlight
                    </button>
                </div>
            --}}
        </div>
    </div>

    {{-- Content area: overview totals vs. details --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pb-0">
            <ul class="nav nav-tabs small" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if (!$deptId) active @endif" data-bs-toggle="tab"
                        data-bs-target="#tabOverview" type="button" role="tab"
                        aria-selected="{{ $deptId ? 'false' : 'true' }}">
                        <i class="bi bi-table me-1"></i> Overview
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link @if ($deptId) active @endif" data-bs-toggle="tab"
                        data-bs-target="#tabDetail" type="button" role="tab"
                        aria-selected="{{ $deptId ? 'true' : 'false' }}">
                        <i class="bi bi-list-ul me-1"></i> Details
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body pt-3">
            <div class="tab-content">
                {{-- OVERVIEW TAB --}}
                <div class="tab-pane fade @if (!$deptId) show active @endif" id="tabOverview"
                    role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 240px;">Department</th>
                                    <th>Dept No</th>
                                    <th class="text-end" style="min-width: 160px;">Total Expense</th>
                                    <th class="text-end" style="width: 1%;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($totals as $r)
                                    <tr class="row-hoverable">
                                        <td class="fw-semibold">{{ $r->dept_name }}</td>
                                        <td class="text-muted">{{ $r->dept_no }}</td>
                                        <td class="text-end fw-semibold">Rp
                                            {{ number_format($r->total_expense, 0, ',', '.') }}</td>
                                        <td class="text-end">
                                            <button class="btn btn-outline-primary btn-sm"
                                                wire:click="showDetail({{ (int) $r->dept_id }})">
                                                View lines
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="bi bi-inbox me-1"></i> No data for {{ $monthLabel }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if ($totals->count())
                                <tfoot>
                                    <tr class="table-light">
                                        <th colspan="2" class="text-end">Grand Total</th>
                                        <th class="text-end">Rp {{ number_format($grandTotal, 0, ',', '.') }}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- DETAIL TAB --}}
                <div class="tab-pane fade @if ($deptId) show active @endif" id="tabDetail"
                    role="tabpanel">
                    @if (!$deptId)
                        <div class="alert alert-info small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            Click a bar in the chart or the “View lines” button in the table to see details.
                        </div>
                    @else
                        <livewire:reports.department-expense-detail-table :dept-id="$deptId" :month="$month"
                            :dept-name="$deptSelected ?? $deptId" :month-label="$monthLabel" :pr-signer="$prSigner" :key="'detail-' . $deptId . '-' . $month . '-' . ($prSigner ?? 'all')" />
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@pushOnce('extraCss')
    <style>
        /* Hero styling */
        .design-hero {
            background:
                radial-gradient(1200px 200px at -10% -20%, rgba(99, 102, 241, 0.15), transparent 60%),
                radial-gradient(800px 200px at 110% 120%, rgba(34, 197, 94, 0.12), transparent 60%),
                linear-gradient(0deg, #fff, #fff);
        }

        .hero-avatar {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #6366f1 0%, #22c55e 100%);
            color: #fff;
            font-size: 1.2rem;
            box-shadow: 0 8px 24px rgba(99, 102, 241, .15);
        }

        .chart-shell {
            height: 380px;
        }
    </style>
@endPushOnce

@pushOnce('extraJs')
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

                    // Livewire -> clear highlight (back button / month change)
                    window.addEventListener('chart:clearSelection', () => {
                        this.selectedDeptId = null;
                        this.updateChart();
                    });
                },

                setData(payload = {}) {
                    this.labels = Array.isArray(payload.labels) ? payload.labels : [];
                    this.data = Array.isArray(payload.data) ? payload.data.map(Number) : [];
                    this.deptIds = Array.isArray(payload.deptIds) ? payload.deptIds.map(Number) : [];
                    this.selectedDeptId = null;
                },

                // Strong color for selected, soft for others
                makeColors() {
                    const idxSel = this.deptIds.findIndex(id => id === this.selectedDeptId);
                    const base = '54, 162, 235'; // blue-ish
                    const hiBg = `rgba(${base}, 0.9)`;
                    const hiBd = `rgba(${base}, 1)`;
                    const loBg = `rgba(${base}, 0.25)`;
                    const loBd = `rgba(${base}, 0.5)`;

                    return {
                        backgroundColor: this.labels.map((_, i) => i === idxSel ? hiBg : loBg),
                        borderColor: this.labels.map((_, i) => i === idxSel ? hiBd : loBd),
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
                    const col = this.makeColors();

                    this.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: this.labels,
                            datasets: [{
                                label: 'Total Expense (IDR)',
                                data: this.data,
                                borderWidth: 1,
                                backgroundColor: col.backgroundColor,
                                borderColor: col.borderColor,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: (c) => ' ' + c.dataset.label + ': Rp ' + fmt.format(c.parsed.y)
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: (v) => 'Rp ' + fmt.format(v)
                                    }
                                },
                                x: {
                                    ticks: {
                                        autoSkip: false,
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
    </script>
@endPushOnce
