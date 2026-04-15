<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8 min-h-screen bg-slate-50/50">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">IT Performance KPIs</h1>
            <p class="text-sm font-medium text-slate-500 mt-1">
                Auditing metrics for {{ auth()->user()->name }}
                ({{ \Carbon\Carbon::parse($metrics['period']['start'])->format('M Y') }})
            </p>
        </div>
        <div>
            <a href="{{ route('ticketing.list') ?? '#' }}"
                class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-md hover:bg-slate-800 transition-colors">
                <span>View All Tickets</span>
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>

    {{-- KPI Cards (Linear / Stripe style: crisp borders, minimal shadows) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        {{-- Throughput --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm relative overflow-hidden group">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Throughput</h3>
                    <div class="mt-1 text-3xl font-black text-slate-900">{{ $metrics['throughput'] }}</div>
                </div>
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            </div>
            <div class="text-xs font-semibold text-slate-500">Tickets resolved this month</div>
            <!-- Decorative Subtle Sparkline -->
            <div class="absolute bottom-0 left-0 w-full h-8 opacity-20 pointer-events-none">
                <canvas id="sparkline-throughput"></canvas>
            </div>
        </div>

        {{-- SLA Compliance --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm relative overflow-hidden">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">SLA Compliance</h3>
                    <div class="mt-1 flex items-baseline gap-1">
                        <span
                            class="text-3xl font-black @if ($metrics['sla_compliance'] >= 90) text-emerald-600 @else text-rose-600 @endif">
                            {{ $metrics['sla_compliance'] }}%
                        </span>
                    </div>
                </div>
                <div
                    class="p-2 @if ($metrics['sla_compliance'] >= 90) bg-emerald-50 text-emerald-600 @else bg-rose-50 text-rose-600 @endif rounded-lg">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="text-xs font-semibold text-slate-500">Target > 90%</div>
        </div>

        {{-- Re-open Rate --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm relative overflow-hidden">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Re-open Rate</h3>
                    <div
                        class="mt-1 text-3xl font-black @if ($metrics['reopen_rate'] > 5) text-rose-600 @else text-slate-900 @endif">
                        {{ $metrics['reopen_rate'] }}%
                    </div>
                </div>
                <div class="p-2 bg-amber-50 text-amber-600 rounded-lg">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                </div>
            </div>
            <div class="text-xs font-semibold text-slate-500">Lower is better</div>
        </div>

        {{-- Utilization --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm relative overflow-hidden">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">WIP / Utilization</h3>
                    <div class="mt-1 flex items-center gap-2">
                        <span class="text-3xl font-black text-slate-900">{{ $metrics['utilization'] }}</span>
                        <div class="flex h-2.5 w-24 rounded-full bg-slate-100 overflow-hidden">
                            @php $utilPct = min(($metrics['utilization'] / 10) * 100, 100); @endphp
                            <div class="h-full @if ($utilPct > 80) bg-rose-500 @else bg-sky-500 @endif rounded-full"
                                style="width: {{ $utilPct }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="p-2 bg-sky-50 text-sky-600 rounded-lg">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="text-xs font-semibold text-slate-500">Concurrent active tickets</div>
        </div>
    </div>

    {{-- Example section for future extension: Top categories or open SLA breaches --}}
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
        <div class="px-6 py-5 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-black text-slate-800 tracking-wide">Productivity Trends</h3>
        </div>
        <div class="p-6 h-64">
            <canvas id="performance-chart"></canvas>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Throughput Sparkline
            const sparkCtx = document.getElementById('sparkline-throughput');
            if (sparkCtx) {
                new Chart(sparkCtx, {
                    type: 'line',
                    data: {
                        labels: [1, 2, 3, 4, 5, 6, 7],
                        datasets: [{
                            data: [2, 4, 3, 5, 2, 6, {{ $metrics['throughput'] }}],
                            borderColor: '#6366f1',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                display: false
                            },
                            y: {
                                display: false,
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Main Performance Chart
            const mainCtx = document.getElementById('performance-chart');
            if (mainCtx) {
                new Chart(mainCtx, {
                    type: 'bar',
                    data: {
                        labels: ['SLA Compliance', 'Throughput', 'Re-open Rate (Avg)', 'Utilization'],
                        datasets: [{
                            label: 'Metrics',
                            data: [{{ $metrics['sla_compliance'] }}, {{ $metrics['throughput'] }},
                                {{ $metrics['reopen_rate'] }}, {{ $metrics['utilization'] }}
                            ],
                            backgroundColor: [
                                'rgba(16, 185, 129, 0.2)',
                                'rgba(99, 102, 241, 0.2)',
                                'rgba(245, 158, 11, 0.2)',
                                'rgba(14, 165, 233, 0.2)'
                            ],
                            borderColor: [
                                '#10b981',
                                '#6366f1',
                                '#f59e0b',
                                '#0ea5e9'
                            ],
                            borderWidth: 2,
                            borderRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: '#f1f5f9'
                                },
                                ticks: {
                                    font: {
                                        weight: 'bold'
                                    }
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        weight: 'bold'
                                    }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
@endpush
