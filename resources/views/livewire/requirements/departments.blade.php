{{-- Requirements Departments (Reverse Drill-down) — Livewire component view --}}
{{-- Tailwind, synced with new.layouts.app --}}

@section('title', $req->code . ' — Departments')
@section('page-title', $req->code . ' — Departments')
@section('page-subtitle', 'Which departments comply with this requirement')

<div>
    {{-- Breadcrumb Navigation (Tailwind style) --}}
    <nav class="flex mb-4" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('requirements.index') }}"
                    class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors">
                    Requirements
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="bx bx-chevron-right text-slate-400 text-lg"></i>
                    <a href="{{ route('requirements.edit', $req) }}"
                        class="ml-1 text-sm font-medium text-slate-500 hover:text-indigo-600 md:ml-2 transition-colors">
                        {{ $req->code }}
                    </a>
                </div>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="bx bx-chevron-right text-slate-400 text-lg"></i>
                    <span class="ml-1 text-sm font-medium text-slate-800 md:ml-2">Departments</span>
                </div>
            </li>
        </ol>
    </nav>

    {{-- Page header + Facts --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <div
                class="h-12 w-12 rounded-2xl bg-gradient-to-br from-indigo-500 to-blue-600 flex items-center justify-center shadow-lg shadow-indigo-200 shrink-0">
                <i class="bx bx-buildings text-white text-2xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800">{{ $req->name }}</h1>
                <div class="flex flex-wrap gap-2 mt-1.5">
                    <span
                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                        Min {{ $req->min_count }}
                    </span>
                    <span
                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        {{ $req->validity_days ? "Valid ≤ {$req->validity_days} days" : 'No expiry' }}
                    </span>
                    @php
                        $freqColors = [
                            'once' => 'bg-slate-100 text-slate-600 border-slate-200',
                            'yearly' => 'bg-sky-50 text-sky-700 border-sky-200',
                            'quarterly' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'monthly' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        ];
                        $freqColor = $freqColors[$req->frequency] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                    @endphp
                    <span
                        class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $freqColor }} border">
                        {{ ucfirst($req->frequency) }}
                    </span>
                    @if ($req->requires_approval)
                        <span
                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold bg-violet-50 text-violet-700 border border-violet-200">
                            <i class="bx bx-shield-alt-2"></i> Approval required
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <a href="{{ route('requirements.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 px-4 py-2 text-sm font-semibold transition-all">
            <i class="bx bx-arrow-back text-base"></i> Back to Requirements
        </a>
    </div>

    {{-- KPI chips --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
        <div class="glass-card px-5 py-4">
            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Assigned To</p>
            <p class="text-3xl font-bold text-slate-800 mt-1">{{ $summary['total'] }} <span
                    class="text-sm font-normal text-slate-500">depts</span></p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4">
            <p class="text-xs font-medium text-emerald-600 uppercase tracking-wide">OK</p>
            <p class="text-3xl font-bold text-emerald-700 mt-1">{{ $summary['ok'] }}</p>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-amber-50 px-5 py-4">
            <p class="text-xs font-medium text-amber-600 uppercase tracking-wide">Pending</p>
            <p class="text-3xl font-bold text-amber-700 mt-1">{{ $summary['pending'] }}</p>
        </div>
        <div class="rounded-2xl border border-rose-100 bg-rose-50 px-5 py-4">
            <p class="text-xs font-medium text-rose-600 uppercase tracking-wide">Missing</p>
            <p class="text-3xl font-bold text-rose-700 mt-1">{{ $summary['missing'] }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="glass-card px-5 py-4 mb-5 flex flex-wrap items-center gap-3">
        {{-- Search --}}
        <div class="relative flex-1 min-w-[200px]">
            <i class="bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search department…"
                class="w-full pl-9 pr-4 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400 outline-none">
        </div>

        {{-- Status filter --}}
        <select wire:model.live="status"
            class="rounded-xl border border-slate-200 text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 outline-none">
            <option value="all">All statuses</option>
            <option value="ok">OK</option>
            <option value="pending">Pending</option>
            <option value="missing">Missing</option>
        </select>

        {{-- Per page --}}
        <select wire:model.live="perPage"
            class="rounded-xl border border-slate-200 text-sm py-2 px-3 focus:ring-2 focus:ring-indigo-400 outline-none">
            <option>10</option>
            <option>25</option>
            <option>50</option>
        </select>
    </div>

    {{-- Departments List --}}
    <div class="glass-card overflow-hidden">
        <div class="divide-y divide-slate-50">
            @forelse($items as $row)
                @php
                    // Compute percent visually for progress bar
                    $p = min(100, (int) round(($row['valid'] / max(1, $row['min'])) * 100));
                    $statusColors = [
                        'OK' => ['bg-emerald-100', 'text-emerald-700'],
                        'Pending' => ['bg-amber-100', 'text-amber-700'],
                        'Missing' => ['bg-rose-100', 'text-rose-700'],
                    ];
                    [$sbg, $stxt] = $statusColors[$row['status']] ?? ['bg-slate-100', 'text-slate-600'];
                    $barColor = $p >= 100 ? 'bg-emerald-500' : ($p > 0 ? 'bg-amber-400' : 'bg-slate-200');
                @endphp
                <div class="flex flex-wrap items-center justify-between gap-4 px-5 py-4 hover:bg-slate-50/50 transition-colors"
                    wire:key="dept-row-{{ $row['dept']->id }}">
                    {{-- Department Info --}}
                    <div class="min-w-0 flex-1 flex items-center gap-4">
                        <div
                            class="h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-100 to-blue-100 flex items-center justify-center shrink-0 font-bold text-indigo-600 text-sm">
                            {{ strtoupper(mb_substr($row['dept']->name, 0, 2)) }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-0.5">
                                <p class="text-sm font-semibold text-slate-800">{{ $row['dept']->name }}</p>
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $sbg }} {{ $stxt }}">
                                    {{ $row['status'] }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400">{{ $row['dept']->code ?? '—' }}</p>
                        </div>
                    </div>

                    {{-- Progress --}}
                    <div class="flex flex-col items-end gap-1.5 shrink-0 w-48 hidden sm:flex">
                        <div class="flex items-center justify-between w-full">
                            <span class="text-xs text-slate-500">Progress</span>
                            <span class="text-xs font-semibold text-slate-700">{{ $row['valid'] }} /
                                {{ $row['min'] }}</span>
                        </div>
                        <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                            <div class="{{ $barColor }} h-full rounded-full transition-all"
                                style="width: {{ $p }}%"></div>
                        </div>
                        @if (($row['pending'] ?? 0) > 0)
                            <span
                                class="text-[10px] font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-100">
                                {{ $row['pending'] }} pending approval
                            </span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <button x-data
                            x-on:click="$dispatch('open-upload', { requirementId: {{ $req->id }}, departmentId: {{ $row['dept']->id }} })"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-600 px-3 py-1.5 text-xs font-semibold shadow-sm transition-all">
                            <i class="bx bx-upload text-sm"></i> Upload
                        </button>
                        <a href="{{ route('departments.compliance', $row['dept']) }}"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 text-xs font-semibold shadow-sm shadow-indigo-200 transition-all">
                            Manage <i class="bx bx-chevron-right text-sm"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center">
                    <i class="bx bx-ghost text-4xl text-slate-300"></i>
                    <p class="text-sm font-medium text-slate-500 mt-2">No departments assigned to this requirement.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-4 flex justify-between items-center">
        <p class="text-xs text-slate-400">
            Showing {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} of {{ $items->total() ?? 0 }}
        </p>
        {{ $items->onEachSide(1)->links('pagination::tailwind') }}
    </div>

    {{-- Single uploader instance --}}
    <livewire:requirements.upload />

</div>

@push('scripts')
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('upload:done', () => {
                location.reload(); // Reloads the entire page to reflect computed statuses
            });
        });
    </script>
@endpush
