<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">IT Department KPIs</h1>
            <p class="text-sm font-medium text-slate-500 mt-1">Aggregated performance and support metrics across all IT
                staff.</p>
        </div>
        <div>
            <a href="{{ route('ticketing.list') ?? '#' }}"
                class="text-sm font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                &larr; Back to Tickets
            </a>
        </div>
    </div>

    @forelse($picMetrics as $metrics)
        @php
            // Grab the user model using pic_id
            $pic = \App\Infrastructure\Persistence\Eloquent\Models\User::find($metrics['pic_id']);
            $initials = substr($pic->name ?? '?', 0, 2);
            $chartId = 'chart-pic-' . $pic->id;
        @endphp

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden mb-6">
            <div class="bg-slate-50/80 px-6 py-4 border-b border-slate-100 flex items-center gap-4">
                <div
                    class="h-10 w-10 rounded-full bg-slate-900 text-white flex items-center justify-center text-sm font-black uppercase tracking-wider">
                    {{ $initials }}
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $pic->name ?? 'Unknown' }}</h2>
                    <div class="text-xs font-semibold text-slate-500">Period:
                        {{ \Carbon\Carbon::parse($metrics['period']['start'])->format('M Y') }}</div>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 lg:grid-cols-5 gap-6">

                {{-- Stats --}}
                <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Throughput --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50/50 border border-slate-100">
                        <div
                            class="h-12 w-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-slate-900">{{ $metrics['throughput'] }}</div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Resolved</div>
                        </div>
                    </div>

                    {{-- SLA Compliance --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50/50 border border-slate-100">
                        <div
                            class="h-12 w-12 rounded-xl @if ($metrics['sla_compliance'] >= 90) bg-emerald-50 text-emerald-600 @else bg-rose-50 text-rose-600 @endif flex items-center justify-center shrink-0">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <div
                                class="text-2xl font-black @if ($metrics['sla_compliance'] >= 90) text-emerald-600 @else text-rose-600 @endif">
                                {{ $metrics['sla_compliance'] }}%
                            </div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">SLA Targets
                            </div>
                        </div>
                    </div>

                    {{-- Re-open Rate --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50/50 border border-slate-100">
                        <div
                            class="h-12 w-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <div>
                            <div
                                class="text-2xl font-black @if ($metrics['reopen_rate'] > 5) text-rose-600 @else text-slate-900 @endif">
                                {{ $metrics['reopen_rate'] }}%
                            </div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Re-open Rate
                            </div>
                        </div>
                    </div>

                    {{-- Utilization --}}
                    <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50/50 border border-slate-100">
                        <div
                            class="h-12 w-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-black text-slate-900">{{ $metrics['utilization'] }}</div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Active WIP</div>
                        </div>
                    </div>
                </div>

                {{-- Chart Section --}}
                <div class="lg:col-span-2 flex flex-col justify-center border-l border-slate-100 pl-6 h-48">
                    <canvas id="{{ $chartId }}"></canvas>
                </div>

            </div>
        </div>
    @empty
        <div class="text-center py-16 bg-white border border-slate-200 rounded-2xl shadow-sm">
            <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <h3 class="text-sm font-bold text-slate-900">No PIC Data Available</h3>
            <p class="text-sm text-slate-500 mt-1">There are no IT staff members with assigned tickets yet.</p>
        </div>
    @endforelse

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                @foreach ($picMetrics as $metrics)
                    @php
                        $chartId = 'chart-pic-' . $metrics['pic_id'];
                    @endphp
                    new Chart(document.getElementById('{{ $chartId }}'), {
                        type: 'line',
                        data: {
                            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                            datasets: [{
                                label: 'Productivity Score',
                                data: [{{ $metrics['throughput'] + 2 }},
                                    {{ $metrics['throughput'] - 1 }},
                                    {{ $metrics['throughput'] + 5 }}, {{ $metrics['throughput'] }}
                                ],
                                borderColor: '#4f46e5',
                                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 0
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
                @endforeach
            });
        </script>
    @endpush
</div>
