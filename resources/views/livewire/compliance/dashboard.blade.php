<div class="container py-4" wire:poll.60s> {{-- auto refresh every 60s --}}

    {{-- Header --}}
    <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-3">
            <h1 class="h5 mb-0">Compliance Dashboard</h1>
            <span class="badge text-dark bg-dark-subtle">Updated {{ $lastUpdated?->diffForHumans() ?? '—' }}</span>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" wire:click="exportCsv">
                <i class="bi bi-download me-1"></i>Export CSV
            </button>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-lg-5">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" class="form-control ps-5" placeholder="Search department…"
                            wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-12 col-lg-7 d-flex flex-wrap gap-2 justify-content-lg-end">
                    {{-- Quick bucket chips --}}
                    @php $b = $bucket; @endphp
                    <button class="btn btn-sm {{ $b === '' ? 'btn-primary' : 'btn-outline-primary' }}"
                        wire:click="$set('bucket','')">All</button>
                    <button class="btn btn-sm {{ $b === '0-49' ? 'btn-danger' : 'btn-outline-danger' }}"
                        wire:click="$set('bucket','0-49')">0–49%</button>
                    <button class="btn btn-sm {{ $b === '50-99' ? 'btn-warning' : 'btn-outline-warning' }}"
                        wire:click="$set('bucket','50-99')">50–99%</button>
                    <button class="btn btn-sm {{ $b === '100' ? 'btn-success' : 'btn-outline-success' }}"
                        wire:click="$set('bucket','100')">100%</button>
                    <div class="form-check ms-2">
                        <input class="form-check-input" type="checkbox" id="hideComplete"
                            wire:model.live="hideComplete">
                        <label class="form-check-label small" for="hideComplete">Hide complete</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-lg-3">
            <button class="card border-0 shadow-sm h-100 w-100 text-start btn p-0"
                wire:click="$set('bucket',''); $set('hideComplete', false)">
                <div class="card-body">
                    <div class="small text-muted">Departments (filtered)</div>
                    <div class="fs-4 fw-semibold">{{ $kpi['count'] }}</div>
                </div>
            </button>
        </div>

        <div class="col-sm-6 col-lg-3">
            <button class="card border-0 shadow-sm h-100 w-100 text-start btn p-0"
                wire:click="$set('bucket',''); $set('hideComplete', false)">
                <div class="card-body">
                    <div class="small text-muted">Average compliance</div>
                    <div class="fs-4 fw-semibold">{{ $kpi['avg'] }}%</div>
                </div>
            </button>
        </div>

        <div class="col-sm-6 col-lg-3">
            <button class="card border-0 shadow-sm h-100 w-100 text-start btn p-0"
                wire:click="$set('bucket','100'); $set('hideComplete', false)">
                <div class="card-body">
                    <div class="small text-muted">100% complete</div>
                    <div class="fs-4 fw-semibold text-success">{{ $kpi['complete'] }}</div>
                </div>
            </button>
        </div>

        <div class="col-sm-6 col-lg-3">
            <button class="card border-0 shadow-sm h-100 w-100 text-start btn p-0"
                wire:click="$set('bucket','0-49'); $set('hideComplete', false)">
                <div class="card-body">
                    <div class="small text-muted">At risk (≤ 49%)</div>
                    <div class="fs-4 fw-semibold text-danger">{{ $kpi['below50'] }}</div>
                </div>
            </button>
        </div>
    </div>


    {{-- Distribution + Trend --}}
    <div class="row g-3 mb-3">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="h6 mb-0">Distribution (filtered)</div>
                        <div class="small text-muted">{{ $dist['total'] }} depts</div>
                    </div>
                    {{-- stacked progress --}}
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-danger" style="width: {{ $dist['p0_49'] }}%" title="0–49%"></div>
                        <div class="progress-bar bg-warning" style="width: {{ $dist['p50_99'] }}%" title="50–99%">
                        </div>
                        <div class="progress-bar bg-success" style="width: {{ $dist['p100'] }}%" title="100%"></div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted mt-2">
                        <span>0–49%: {{ $dist['c0_49'] }}</span>
                        <span>50–99%: {{ $dist['c50_99'] }}</span>
                        <span>100%: {{ $dist['c100'] }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="h6 mb-0">Avg compliance — last 12 months</div>
                    </div>
                    <canvas id="trendChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        @php
            // utility: make a minimal path for sparkline (0..100 -> SVG height)
            function sparkPath($points, $w = 80, $h = 24)
            {
                $n = max(count($points), 1);
                $dx = $n > 1 ? $w / ($n - 1) : 0;
                $path = [];
                foreach ($points as $i => $p) {
                    $x = $i * $dx;
                    $y = $h - ($p / 100) * $h; // invert Y (0 bottom)
                    $path[] = ($i === 0 ? 'M' : 'L') . round($x, 1) . ' ' . round($y, 1);
                }
                return implode(' ', $path);
            }
        @endphp

        {{-- Bottom 10 --}}
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="h6 mb-3">Bottom 10 departments</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                <tr>
                                    <th>Department</th>
                                    <th>Spark</th>
                                    <th class="text-end">Percent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bottom as $row)
                                    @php $pts = $sparklines[$row->department_id] ?? []; @endphp
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $row->department->name }}</div>
                                            <div class="small text-muted">{{ $row->department->code ?? '—' }}</div>
                                        </td>
                                        <td style="width:100px">
                                            <svg width="100" height="24" viewBox="0 0 80 24"
                                                preserveAspectRatio="none">
                                                <path d="{{ sparkPath($pts, 80, 24) }}" fill="none"
                                                    stroke="currentColor" stroke-width="1.5"
                                                    class="{{ end($pts) === 100 ? 'text-success' : 'text-secondary' }}" />
                                            </svg>
                                        </td>
                                        <td class="text-end">
                                            <span
                                                class="badge {{ $row->percent == 100 ? 'text-bg-success' : 'text-bg-warning' }}">{{ $row->percent }}%</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top 10 --}}
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="h6 mb-3">Top 10 departments</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Department</th>
                                    <th>Spark</th>
                                    <th class="text-end">Percent</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($top as $row)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $row->department->name }}</div>
                                            <div class="small text-muted">{{ $row->department->code ?? '—' }}</div>
                                        </td>
                                        <td style="width:100px">
                                            <svg width="100" height="24" viewBox="0 0 80 24"
                                                preserveAspectRatio="none">
                                                <path d="{{ sparkPath($pts, 80, 24) }}" fill="none"
                                                    stroke="currentColor" stroke-width="1.5"
                                                    class="{{ end($pts) === 100 ? 'text-success' : 'text-secondary' }}" />
                                            </svg>
                                        </td>
                                        <td class="text-end">
                                            <span
                                                class="badge {{ $row->percent == 100 ? 'text-bg-success' : 'text-bg-primary-subtle' }}">{{ $row->percent }}%</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        {{-- Pending Approvals --}}
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="h6 mb-3">Pending approvals</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Department</th>
                                    <th>Requirement</th>
                                    <th class="text-end">Uploaded</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pending as $u)
                                    <tr>
                                        <td class="small">{{ $u->dept_name }}</td>
                                        <td class="small">{{ $u->req_code }} — {{ $u->req_name }}</td>
                                        <td class="small text-end">{{ $u->created_at->diffForHumans() }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No pending approvals
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Expiring Soon --}}
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="h6 mb-3">Expiring within 30 days</div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Department</th>
                                    <th>Requirement</th>
                                    <th class="text-end">Expires</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiring as $u)
                                    <tr>
                                        <td class="small">{{ $u->dept_name }}</td>
                                        <td class="small">{{ $u->req_code }} — {{ $u->req_name }}</td>
                                        <td class="small text-end">{{ optional($u->valid_until)->format('Y-m-d') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">Nothing expiring soon
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading overlay --}}
    <div wire:loading.delay>
        <div class="position-fixed top-0 start-0 w-100 h-100"
            style="background: rgba(255,255,255,.5); z-index: 1050;">
            <div class="position-absolute top-50 start-50 translate-middle text-muted">
                <div class="spinner-border spinner-border-sm me-2"></div> Updating…
            </div>
        </div>
    </div>
</div>

@pushOnce('extraJs')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            const ctx = document.getElementById('trendChart').getContext('2d');
            window._complianceTrend?.destroy?.();
            window._complianceTrend = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($trendLabels),
                    datasets: [{
                        label: 'Avg %',
                        data: @json($trendValues),
                        fill: false,
                        tension: 0.2
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            min: 0,
                            max: 100,
                            ticks: {
                                stepSize: 20
                            }
                        }
                    }
                }
            });
        });
    </script>
@endPushOnce
