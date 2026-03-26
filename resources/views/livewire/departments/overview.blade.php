{{-- Departments Overview — Livewire component view --}}
{{-- Tailwind, synced with new.layouts.app --}}

@section('title', 'Departments — Compliance')
@section('page-title', 'Departments')
@section('page-subtitle', 'Compliance status per department')

<div>
    {{-- Page header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-200 shrink-0">
                <i class="bx bx-buildings text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">Departments</h1>
                <p class="text-sm text-slate-500 mt-0.5">{{ $items->total() }} departments tracked</p>
            </div>
        </div>
        <a href="{{ route('compliance.dashboard') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 shadow-sm transition-all">
            <i class="bx bx-bar-chart-alt text-base"></i> Dashboard
        </a>
    </div>

    {{-- KPI chips --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="glass-card px-5 py-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">This page</p>
            <p class="text-3xl font-bold text-slate-800 mt-1">{{ $kpi['count'] }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4">
            <p class="text-xs font-medium text-emerald-600 uppercase tracking-wide">Complete</p>
            <p class="text-3xl font-bold text-emerald-700 mt-1">{{ $kpi['complete'] }}</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50 px-5 py-4">
            <p class="text-xs font-medium text-amber-600 uppercase tracking-wide">Incomplete</p>
            <p class="text-3xl font-bold text-amber-700 mt-1">{{ $kpi['incomplete'] }}</p>
        </div>
        <div class="glass-card px-5 py-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Avg Compliance</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $kpi['avg'] }}%</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="glass-card px-5 py-4 mb-5 flex flex-wrap items-center gap-3">
        {{-- Search --}}
        <div class="relative flex-1 min-w-[180px]">
            <i class="bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
            <input type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search by name or code…"
                class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none">
        </div>

        {{-- Status filter --}}
        <select wire:model.live="status"
            class="rounded-xl border border-slate-200 text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none">
            <option value="all">All status</option>
            <option value="complete">Complete (100%)</option>
            <option value="incomplete">Incomplete</option>
        </select>

        {{-- Bucket filter --}}
        <select wire:model.live="bucket"
            class="rounded-xl border border-slate-200 text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none">
            <option value="">All buckets</option>
            <option value="0-49">0–49%</option>
            <option value="50-99">50–99%</option>
            <option value="100">100%</option>
        </select>

        {{-- Sort + direction --}}
        <div class="flex items-center gap-2">
            <select wire:model.live="sort"
                class="rounded-xl border border-slate-200 text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 outline-none">
                <option value="name">Name</option>
                <option value="code">Code</option>
                <option value="percent">% Compliance</option>
            </select>
            <button wire:click="toggleDir"
                class="h-9 w-9 flex items-center justify-center rounded-xl border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors">
                <i class="bx {{ $dir === 'asc' ? 'bx-sort-up' : 'bx-sort-down' }} text-lg"></i>
            </button>
        </div>

        {{-- Per page --}}
        <select wire:model.live="perPage"
            class="rounded-xl border border-slate-200 text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 outline-none">
            <option>10</option>
            <option>25</option>
            <option>50</option>
        </select>
    </div>

    {{-- Department list --}}
    <div class="glass-card overflow-hidden">
        <div class="divide-y divide-slate-50">
            @forelse($items as $row)
                @php
                    $p = $row['percent'];
                    $statusColor = $p >= 100 ? 'emerald' : ($p < 50 ? 'rose' : 'amber');
                @endphp
                <a href="{{ route('departments.compliance', $row['dept']) }}"
                    wire:key="dept-{{ $row['dept']->id }}"
                    class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50/70 transition-colors group">

                    {{-- Avatar --}}
                    <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-100 to-blue-100 flex items-center justify-center shrink-0 font-bold text-indigo-600 text-sm group-hover:scale-105 transition-transform">
                        {{ strtoupper(mb_substr($row['dept']->name, 0, 2)) }}
                    </div>

                    {{-- Name + code --}}
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $row['dept']->name }}</p>
                        <p class="text-xs text-slate-400">{{ $row['dept']->code ?? '—' }}</p>
                    </div>

                    {{-- Progress bar --}}
                    <div class="hidden sm:flex items-center gap-3 w-52 shrink-0">
                        <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all bg-{{ $statusColor }}-500"
                                style="width: {{ $p }}%"></div>
                        </div>
                        <span class="text-xs font-semibold text-slate-600 w-9 text-right">{{ $p }}%</span>
                    </div>

                    {{-- Status badge --}}
                    <span class="shrink-0 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                        bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700">
                        {{ $row['status'] }}
                    </span>

                    {{-- Arrow --}}
                    <i class="bx bx-chevron-right text-slate-300 group-hover:text-indigo-400 transition-colors text-xl shrink-0"></i>
                </a>
            @empty
                <div class="py-16 text-center">
                    <i class="bx bx-search text-4xl text-slate-300"></i>
                    <p class="text-sm text-slate-400 mt-2">No departments match your filters.</p>
                    <button wire:click="$set('search', ''); $set('status', 'all'); $set('bucket', '')"
                        class="mt-3 text-xs text-indigo-600 hover:underline">Clear filters</button>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4">
        {{ $items->links() }}
    </div>
</div>
