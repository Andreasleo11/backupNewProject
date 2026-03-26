{{-- Compliance Dashboard — Livewire component view --}}
{{-- Tailwind, synced with new.layouts.app --}}

@section('title', 'Compliance Dashboard')
@section('page-title', 'Compliance Dashboard')
@section('page-subtitle', 'Document compliance status across all departments')

<div wire:poll.60s>

    {{-- ─── Page header ────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg shadow-indigo-200 shrink-0">
                <i class="bx bxs-shield-alt-2 text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Compliance Dashboard</h1>
                <p class="text-sm text-slate-500 mt-0.5">
                    @if($lastUpdated)
                        Updated {{ $lastUpdated->diffForHumans() }}
                    @else
                        No snapshot data yet
                    @endif
                </p>
            </div>
        </div>
        <button wire:click="exportCsv"
            class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 hover:border-slate-300 shadow-sm transition-all">
            <i class="bx bx-download text-base"></i> Export Excel
        </button>
    </div>

    {{-- ─── Toolbar ────────────────────────────────────────────────── --}}
    <div class="glass-card px-5 py-4 mb-5 flex flex-wrap items-center gap-3">
        {{-- Search --}}
        <div class="relative flex-1 min-w-[200px]">
            <i class="bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
            <input type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search department…"
                class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none">
        </div>

        {{-- Bucket chips --}}
        <div class="flex flex-wrap gap-2">
            @foreach (['' => 'All', '0-49' => '0–49%', '50-99' => '50–99%', '100' => '100%'] as $val => $label)
                @php
                    $active = $bucket === $val;
                    $colors = match($val) {
                        '0-49'  => $active ? 'bg-rose-500 text-white border-rose-500' : 'border-rose-200 text-rose-600 hover:bg-rose-50',
                        '50-99' => $active ? 'bg-amber-500 text-white border-amber-500' : 'border-amber-200 text-amber-600 hover:bg-amber-50',
                        '100'   => $active ? 'bg-emerald-500 text-white border-emerald-500' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50',
                        default => $active ? 'bg-indigo-600 text-white border-indigo-600' : 'border-slate-200 text-slate-600 hover:bg-slate-50',
                    };
                @endphp
                <button wire:click="$set('bucket', '{{ $val }}')"
                    class="rounded-full border px-3 py-1 text-xs font-semibold transition-all {{ $colors }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Hide complete toggle --}}
        <label class="flex items-center gap-2 cursor-pointer select-none">
            <div class="relative">
                <input type="checkbox" wire:model.live="hideComplete" class="sr-only peer">
                <div class="w-9 h-5 rounded-full bg-slate-200 peer-checked:bg-indigo-500 transition-colors"></div>
                <div class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></div>
            </div>
            <span class="text-xs font-medium text-slate-600">Hide complete</span>
        </label>
    </div>

    {{-- ─── KPI cards ───────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <button wire:click="$set('bucket', ''); $set('hideComplete', false)"
            class="glass-card px-5 py-4 text-left hover:-translate-y-0.5 transition-transform">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Departments</p>
            <p class="text-3xl font-bold text-slate-800 mt-1">{{ $kpi['count'] }}</p>
        </button>

        <button wire:click="$set('bucket', ''); $set('hideComplete', false)"
            class="glass-card px-5 py-4 text-left hover:-translate-y-0.5 transition-transform">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Avg Compliance</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $kpi['avg'] }}%</p>
        </button>

        <button wire:click="$set('bucket', '100'); $set('hideComplete', false)"
            class="rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-left hover:-translate-y-0.5 transition-transform">
            <p class="text-xs font-medium text-emerald-600 uppercase tracking-wide">100% Complete</p>
            <p class="text-3xl font-bold text-emerald-700 mt-1">{{ $kpi['complete'] }}</p>
        </button>

        <button wire:click="$set('bucket', '0-49'); $set('hideComplete', false)"
            class="rounded-2xl border border-rose-100 bg-rose-50 px-5 py-4 text-left hover:-translate-y-0.5 transition-transform">
            <p class="text-xs font-medium text-rose-600 uppercase tracking-wide">At Risk (≤49%)</p>
            <p class="text-3xl font-bold text-rose-700 mt-1">{{ $kpi['below50'] }}</p>
        </button>
    </div>

    {{-- ─── Distribution + Trend ───────────────────────────────────── --}}
    <div class="grid lg:grid-cols-5 gap-4 mb-5">
        {{-- Distribution bar --}}
        <div class="glass-card px-5 py-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-slate-700">Distribution</p>
                <span class="text-xs text-slate-400">{{ $dist['total'] }} depts</span>
            </div>
            <div class="flex h-4 rounded-full overflow-hidden gap-0.5">
                @if($dist['p0_49'] > 0)
                    <div class="bg-rose-400 rounded-l-full transition-all" style="width:{{ $dist['p0_49'] }}%"
                        title="0–49%: {{ $dist['c0_49'] }}"></div>
                @endif
                @if($dist['p50_99'] > 0)
                    <div class="bg-amber-400 transition-all" style="width:{{ $dist['p50_99'] }}%"
                        title="50–99%: {{ $dist['c50_99'] }}"></div>
                @endif
                @if($dist['p100'] > 0)
                    <div class="bg-emerald-400 transition-all {{ !$dist['p0_49'] && !$dist['p50_99'] ? 'rounded-l-full' : '' }} rounded-r-full" style="width:{{ $dist['p100'] }}%"
                        title="100%: {{ $dist['c100'] }}"></div>
                @endif
            </div>
            <div class="flex justify-between items-center mt-3 text-xs">
                <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-rose-400 inline-block"></span>
                    <span class="text-slate-500">0–49%: <strong class="text-slate-700">{{ $dist['c0_49'] }}</strong></span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-amber-400 inline-block"></span>
                    <span class="text-slate-500">50–99%: <strong class="text-slate-700">{{ $dist['c50_99'] }}</strong></span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-emerald-400 inline-block"></span>
                    <span class="text-slate-500">100%: <strong class="text-slate-700">{{ $dist['c100'] }}</strong></span>
                </div>
            </div>
        </div>

        {{-- Trend chart --}}
        <div class="glass-card px-5 py-5 lg:col-span-3">
            <p class="text-sm font-semibold text-slate-700 mb-4">Avg Compliance — Last 12 Months</p>
            <canvas id="complianceTrendChart" height="70"></canvas>
        </div>
    </div>

    {{-- ─── Bottom / Top 10 ─────────────────────────────────────────── --}}
    <div class="grid lg:grid-cols-2 gap-4 mb-5">
        @php
            function sparkPath($points, $w = 80, $h = 24) {
                $n = max(count($points), 1);
                $dx = $n > 1 ? $w / ($n - 1) : 0;
                $path = [];
                foreach ($points as $i => $p) {
                    $x = $i * $dx;
                    $y = $h - ($p / 100) * $h;
                    $path[] = ($i === 0 ? 'M' : 'L') . round($x, 1) . ' ' . round($y, 1);
                }
                return implode(' ', $path);
            }
        @endphp

        @foreach ([['title' => '⚠ Bottom 10', 'rows' => $bottom, 'accent' => 'rose'] , ['title' => '✓ Top 10', 'rows' => $top, 'accent' => 'emerald']] as $panel)
            <div class="glass-card overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-700">{{ $panel['title'] }}</p>
                </div>
                <div class="divide-y divide-slate-50">
                    @forelse($panel['rows'] as $row)
                        @php $pts = $sparklines[$row->department_id] ?? []; @endphp
                        <a href="{{ route('departments.compliance', $row->department) }}"
                            class="flex items-center justify-between px-5 py-3 hover:bg-slate-50/70 transition-colors">
                            <div class="min-w-0 flex-1 pr-4">
                                <p class="text-sm font-medium text-slate-800 truncate">{{ $row->department->name }}</p>
                                <p class="text-xs text-slate-400">{{ $row->department->code ?? '—' }}</p>
                            </div>
                            <svg width="60" height="24" viewBox="0 0 80 24" preserveAspectRatio="none"
                                class="shrink-0 mr-4">
                                <path d="{{ sparkPath($pts, 80, 24) }}" fill="none"
                                    stroke="{{ end($pts) >= 80 ? '#10b981' : ($panel['accent'] === 'rose' ? '#f43f5e' : '#10b981') }}"
                                    stroke-width="2"/>
                            </svg>
                            <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                {{ $row->percent >= 100 ? 'bg-emerald-100 text-emerald-700' : ($row->percent < 50 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700') }}">
                                {{ $row->percent }}%
                            </span>
                        </a>
                    @empty
                        <div class="py-10 text-center">
                            <i class="bx bx-data text-3xl text-slate-300"></i>
                            <p class="text-sm text-slate-400 mt-2">No data available</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>

    {{-- ─── Pending Approvals + Expiring ───────────────────────────── --}}
    <div class="grid lg:grid-cols-2 gap-4">

        {{-- Pending --}}
        <div class="glass-card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-700">Pending Approvals</p>
                @if($pending->count())
                    <a href="{{ route('requirement-uploads.review') }}"
                        class="text-xs text-indigo-600 font-medium hover:underline">View all →</a>
                @endif
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($pending as $u)
                    <div class="flex items-start gap-3 px-5 py-3">
                        <div class="h-2 w-2 rounded-full bg-amber-400 mt-2 shrink-0"></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $u->dept_name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $u->req_code }} — {{ $u->req_name }}</p>
                        </div>
                        <span class="text-xs text-slate-400 shrink-0 pt-0.5">{{ $u->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <i class="bx bx-check-circle text-3xl text-emerald-300"></i>
                        <p class="text-sm text-slate-400 mt-1">No pending approvals</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Expiring --}}
        <div class="glass-card overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <p class="text-sm font-semibold text-slate-700">Expiring Within 30 Days</p>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($expiring as $u)
                    @php $daysLeft = now()->diffInDays($u->valid_until, false); @endphp
                    <div class="flex items-start gap-3 px-5 py-3">
                        <div class="h-2 w-2 rounded-full bg-rose-400 mt-2 shrink-0"></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-slate-700 truncate">{{ $u->dept_name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $u->req_code }} — {{ $u->req_name }}</p>
                        </div>
                        <span class="text-xs font-semibold shrink-0 pt-0.5
                            {{ $daysLeft <= 7 ? 'text-rose-600' : 'text-amber-600' }}">
                            in {{ $daysLeft }}d
                        </span>
                    </div>
                @empty
                    <div class="py-8 text-center">
                        <i class="bx bx-calendar-check text-3xl text-emerald-300"></i>
                        <p class="text-sm text-slate-400 mt-1">Nothing expiring soon</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Loading overlay --}}
    <div wire:loading.delay class="fixed inset-0 z-50 bg-white/40 backdrop-blur-sm flex items-center justify-center">
        <div class="glass-card px-6 py-4 flex items-center gap-3">
            <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span class="text-sm font-medium text-slate-600">Updating…</span>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('livewire:init', () => {
        const ctx = document.getElementById('complianceTrendChart');
        if (!ctx) return;
        window._complianceTrend?.destroy?.();
        window._complianceTrend = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($trendLabels),
                datasets: [{
                    label: 'Avg %',
                    data: @json($trendValues),
                    fill: true,
                    tension: 0.4,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.08)',
                    pointBackgroundColor: '#6366f1',
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    borderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                animation: { duration: 400 },
                plugins: { legend: { display: false }, tooltip: { mode: 'index', intersect: false } },
                scales: {
                    y: { min: 0, max: 100, ticks: { stepSize: 25, callback: v => v + '%' }, grid: { color: '#f1f5f9' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // Re-render chart when Livewire updates the component
        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
            succeed(({ snapshot, effect }) => {
                requestAnimationFrame(() => {
                    if (window._complianceTrend) {
                        window._complianceTrend.data.labels = @json($trendLabels);
                        window._complianceTrend.data.datasets[0].data = @json($trendValues);
                        window._complianceTrend.update('active');
                    }
                });
            });
        });
    });
</script>
@endpush
